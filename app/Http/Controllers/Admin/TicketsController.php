<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Moderation\Exception;
use App\Models\Server;
use App\Models\Ticket;
use App\Models\TicketBlacklist;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\Ticket\User\ReplyNotification;
use App\Settings\LocaleSettings;
use App\Settings\PterodactylSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    const READ_PERMISSION = "admin.tickets.read";
    const WRITE_PERMISSION = "admin.tickets.write";

    const BLACKLIST_READ_PERMISSION ='admin.ticket_blacklist.read';
    const BLACKLIST_WRITE_PERMISSION ='admin.ticket_blacklist.write';
    public function index(LocaleSettings $locale_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);

        return view('admin.ticket.index', [
            'tickets' => Ticket::orderBy('id', 'desc')->paginate(10),
            'ticketcategories' => TicketCategory::all(),
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    public function show($ticket_id, PterodactylSettings $ptero_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);
        try {
        $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
        } catch (Exception $e)
        {
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }
        $ticketcomments = $ticket->ticketcomments;
        $ticketcategory = $ticket->ticketcategory;
        $server = Server::where('id', $ticket->server)->first();
        $pterodactyl_url = $ptero_settings->panel_url;

        return view('admin.ticket.show', compact('ticket', 'ticketcategory', 'ticketcomments', 'server', 'pterodactyl_url'));
    }

    public function changeStatus($ticket_id)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        try {
        $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
        } catch(Exception $e)
        {
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }

        if($ticket->status == "Closed"){
            $ticket->status = "Reopened";
            $ticket->save();
            return redirect()->back()->with('success', __('A ticket has been reopened, ID: #') . $ticket->ticket_id);
        }
        $ticket->status = 'Closed';
        $ticket->save();
        $ticketOwner = $ticket->user;

        return redirect()->back()->with('success', __('A ticket has been closed, ID: #').$ticket->ticket_id);
    }

    public function delete($ticket_id)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        try {
        $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
        } catch (Exception $e)
        {
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }

        TicketComment::where('ticket_id', $ticket->id)->delete();
        $ticket->delete();

        return redirect()->back()->with('success', __('A ticket has been deleted, ID: #').$ticket_id);
    }

    public function reply(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);


        $this->validate($request, ['ticketcomment' => 'required']);
        try {
            $ticket = Ticket::where('id', $request->input('ticket_id'))->firstOrFail();
        } catch (Exception $e){
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }
        $ticket->status = 'Answered';
        $ticket->update();
        TicketComment::create([
            'ticket_id' => $request->input('ticket_id'),
            'user_id' => Auth::user()->id,
            'ticketcomment' => $request->input('ticketcomment'),
        ]);
        try {
        $user = User::where('id', $ticket->user_id)->firstOrFail();
        } catch(Exception $e)
        {
            return redirect()->back()->with('warning', __('User not found on the server. Check on the admin database or try again later.'));
        }
        $newmessage = $request->input('ticketcomment');
        $user->notify(new ReplyNotification($ticket, $user, $newmessage));

        return redirect()->back()->with('success', __('Your comment has been submitted'));
    }

    public function dataTable()
    {
        $query = Ticket::leftJoin('ticket_categories', 'tickets.ticketcategory_id', '=', 'ticket_categories.id')
            ->select(['tickets.*', 'ticket_categories.name as category_name']);

        return datatables($query)
            ->addColumn('category', function (Ticket $ticket) {
                return $ticket->category_name;
            })
            ->editColumn('title', function (Ticket $tickets) {
                return '<a class="text-info"  href="'.route('admin.ticket.show', ['ticket_id' => $tickets->ticket_id]).'">'.'#'.$tickets->ticket_id.' - '.htmlspecialchars($tickets->title).'</a>';
            })
            ->editColumn('user_id', function (Ticket $tickets) {
                return '<a href="'.route('admin.users.show', $tickets->user->id).'">'.$tickets->user->name.'</a>';
            })
            ->addColumn('actions', function (Ticket $tickets) {
                $statusButtonColor = ($tickets->status == "Closed") ? 'btn-success' : 'btn-warning';
                $statusButtonIcon = ($tickets->status == "Closed") ? 'fa-redo' : 'fa-times';
                $statusButtonText = ($tickets->status == "Closed") ? __('Reopen') : __('Close');

                return '
                            <a data-content="'.__('View').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.ticket.show', ['ticket_id' => $tickets->ticket_id]).'" class="mr-1 text-white btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <form class="d-inline"  method="post" action="'.route('admin.ticket.changeStatus', ['ticket_id' => $tickets->ticket_id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__($statusButtonText).'" data-toggle="popover" data-trigger="hover" data-placement="top" class="text-white btn btn-sm '.$statusButtonColor.'  mr-1"><i class="fas '.$statusButtonIcon.'"></i></button>
                            </form>
                            <form class="d-inline"  method="post" action="'.route('admin.ticket.delete', ['ticket_id' => $tickets->ticket_id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 text-white btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                ';
            })
            ->editColumn('status', function (Ticket $tickets) {
                switch ($tickets->status) {
                    case 'Reopened':
                    case 'Open':
                        $badgeColor = 'badge-success';
                        break;
                    case 'Closed':
                        $badgeColor = 'badge-danger';
                        break;
                    case 'Answered':
                        $badgeColor = 'badge-info';
                        break;
                    default:
                        $badgeColor = 'badge-warning';
                        break;
                }

                return '<span class="badge '.$badgeColor.'">'.$tickets->status.'</span>';
            })
            ->editColumn('priority', function (Ticket $tickets) {
                return __($tickets->priority);
            })
            ->editColumn('updated_at', function (Ticket $tickets) {
                return ['display' => $tickets->updated_at ? $tickets->updated_at->diffForHumans() : '',
                        'raw' => $tickets->updated_at ? strtotime($tickets->updated_at) : ''];
            })
            ->orderColumn('category', 'category_name $1')
            ->rawColumns(['title', 'user_id', 'status', 'priority', 'updated_at', 'actions'])
            ->make(true);
    }

    public function blacklist(LocaleSettings $locale_settings)
    {
        $this->checkAnyPermission([self::BLACKLIST_READ_PERMISSION, self::BLACKLIST_WRITE_PERMISSION]);

        return view('admin.ticket.blacklist', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    public function blacklistAdd(Request $request)
    {
        $this->checkPermission(self::BLACKLIST_WRITE_PERMISSION);

        try {
            $user = User::where('id', $request->user_id)->firstOrFail();

            if (auth()->user()->roles()->first()->power < $user->roles()->first()->power) {
                return redirect()->back()->with('warning', __('You cannot blacklist a user with higher power than you.'));
            }

            $check = TicketBlacklist::where('user_id', $user->id)->first();
        }
        catch (Exception $e){
            return redirect()->back()->with('warning', __('User not found on the server. Check the admin database or try again later.'));
        }
        if ($check) {
            $check->reason = $request->reason;
            $check->status = 'True';
            $check->save();

            return redirect()->back()->with('info', __('Target User already in blacklist. Reason updated'));
        }
        TicketBlacklist::create([
            'user_id' => $user->id,
            'status' => 'True',
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', __('Successfully add User to blacklist, User name: '.$user->name));
    }

    public function blacklistDelete($id)
    {
        $this->checkPermission(self::BLACKLIST_WRITE_PERMISSION);

        $blacklist = TicketBlacklist::where('id', $id)->first();
        $blacklist->delete();

        return redirect()->back()->with('success', __('Successfully remove User from blacklist, User name: '.$blacklist->user->name));
    }

    public function blacklistChange($id)
    {
        $this->checkPermission(self::BLACKLIST_WRITE_PERMISSION);

        try {
            $blacklist = TicketBlacklist::where('id', $id)->first();
        }
        catch (Exception $e){
            return redirect()->back()->with('warning', __('User not found on the server. Check the admin database or try again later.'));
        }
        if ($blacklist->status == 'True') {
            $blacklist->status = 'False';
        } else {
            $blacklist->status = 'True';
        }
        $blacklist->update();

        return redirect()->back()->with('success', __('Successfully change status blacklist from, User name: '.$blacklist->user->name));
    }

    public function dataTableBlacklist()
    {
        $query = TicketBlacklist::with(['user']);
        $query->select('ticket_blacklists.*');

        return datatables($query)
            ->editColumn('user', function (TicketBlacklist $blacklist) {
                return '<a href="'.route('admin.users.show', $blacklist->user->id).'">'.$blacklist->user->name.'</a>';
            })
            ->editColumn('status', function (TicketBlacklist $blacklist) {
                switch ($blacklist->status) {
                    case 'True':
                        $text = 'Blocked';
                        $badgeColor = 'badge-danger';
                        break;
                    default:
                        $text = 'Unblocked';
                        $badgeColor = 'badge-success';
                        break;
                }

                return '<span class="badge '.$badgeColor.'">'.$text.'</span>';
            })
            ->editColumn('reason', function (TicketBlacklist $blacklist) {
                return $blacklist->reason;
            })
            ->addColumn('actions', function (TicketBlacklist $blacklist) {
                return '
                            <form class="d-inline"  method="post" action="'.route('admin.ticket.blacklist.change', ['id' => $blacklist->id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Change Status').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 text-white btn btn-sm btn-warning"><i class="fas fa-sync-alt"></i></button>
                            </form>
                            <form class="d-inline"  method="post" action="'.route('admin.ticket.blacklist.delete', ['id' => $blacklist->id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 text-white btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                ';
            })
            ->editColumn('created_at', function (TicketBlacklist $blacklist) {
                return $blacklist->created_at ? $blacklist->created_at->diffForHumans() : '';
            })
            ->rawColumns(['user', 'status', 'reason', 'created_at', 'actions'])
            ->make(true);
    }
}
