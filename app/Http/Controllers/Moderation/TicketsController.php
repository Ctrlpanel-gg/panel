<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Ticket;
use App\Models\TicketBlacklist;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\Ticket\User\ReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function index()
    {
        $tickets = Ticket::orderBy('id', 'desc')->paginate(10);
        $ticketcategories = TicketCategory::all();

        return view('moderator.ticket.index', compact('tickets', 'ticketcategories'));
    }

    public function show($ticket_id)
    {
        $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
        $ticketcomments = $ticket->ticketcomments;
        $ticketcategory = $ticket->ticketcategory;
        $server = Server::where('id', $ticket->server)->first();

        return view('moderator.ticket.show', compact('ticket', 'ticketcategory', 'ticketcomments', 'server'));
    }

    public function close($ticket_id)
    {
        $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
        $ticket->status = 'Closed';
        $ticket->save();
        $ticketOwner = $ticket->user;

        return redirect()->back()->with('success', __('A ticket has been closed, ID: #').$ticket->ticket_id);
    }

    public function delete($ticket_id)
    {
        $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
        TicketComment::where('ticket_id', $ticket->id)->delete();
        $ticket->delete();

        return redirect()->back()->with('success', __('A ticket has been deleted, ID: #').$ticket_id);
    }

    public function reply(Request $request)
    {
        $this->validate($request, ['ticketcomment' => 'required']);
        $ticket = Ticket::where('id', $request->input('ticket_id'))->firstOrFail();
        $ticket->status = 'Answered';
        $ticket->update();
        TicketComment::create([
            'ticket_id' => $request->input('ticket_id'),
            'user_id' => Auth::user()->id,
            'ticketcomment' => $request->input('ticketcomment'),
        ]);
        $user = User::where('id', $ticket->user_id)->firstOrFail();
        $newmessage = $request->input('ticketcomment');
        $user->notify(new ReplyNotification($ticket, $user, $newmessage));

        return redirect()->back()->with('success', __('Your comment has been submitted'));
    }

    public function dataTable()
    {
        $query = Ticket::query();

        return datatables($query)
            ->addColumn('category', function (Ticket $tickets) {
                return $tickets->ticketcategory->name;
            })
            ->editColumn('title', function (Ticket $tickets) {
                return '<a class="text-info"  href="'.route('moderator.ticket.show', ['ticket_id' => $tickets->ticket_id]).'">'.'#'.$tickets->ticket_id.' - '.$tickets->title.'</a>';
            })
            ->editColumn('user_id', function (Ticket $tickets) {
                return '<a href="'.route('admin.users.show', $tickets->user->id).'">'.$tickets->user->name.'</a>';
            })
            ->addColumn('actions', function (Ticket $tickets) {
                return '
                            <a data-content="'.__('View').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('moderator.ticket.show', ['ticket_id' => $tickets->ticket_id]).'" class="btn btn-sm text-white btn-info mr-1"><i class="fas fa-eye"></i></a>
                            <form class="d-inline"  method="post" action="'.route('moderator.ticket.close', ['ticket_id' => $tickets->ticket_id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Close').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm text-white btn-warning mr-1"><i class="fas fa-times"></i></button>
                            </form>
                            <form class="d-inline"  method="post" action="'.route('moderator.ticket.delete', ['ticket_id' => $tickets->ticket_id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm text-white btn-danger mr-1"><i class="fas fa-trash"></i></button>
                            </form>
                ';
            })
            ->editColumn('status', function (Ticket $tickets) {
                switch ($tickets->status) {
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
            ->editColumn('updated_at', function (Ticket $tickets) {
                return $tickets->updated_at ? $tickets->updated_at->diffForHumans() : '';
            })
            ->rawColumns(['category', 'title', 'user_id', 'status', 'updated_at', 'actions'])
            ->make(true);
    }

    public function blacklist()
    {
        return view('moderator.ticket.blacklist');
    }

    public function blacklistAdd(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $check = TicketBlacklist::where('user_id', $user->id)->first();
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
        $blacklist = TicketBlacklist::where('id', $id)->first();
        $blacklist->delete();

        return redirect()->back()->with('success', __('Successfully remove User from blacklist, User name: '.$blacklist->user->name));
    }

    public function blacklistChange($id)
    {
        $blacklist = TicketBlacklist::where('id', $id)->first();
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
                            <form class="d-inline"  method="post" action="'.route('moderator.ticket.blacklist.change', ['id' => $blacklist->id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Change Status').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm text-white btn-warning mr-1"><i class="fas fa-sync-alt"></i></button>
                            </form>
                            <form class="d-inline"  method="post" action="'.route('moderator.ticket.blacklist.delete', ['id' => $blacklist->id]).'">
                                '.csrf_field().'
                                '.method_field('POST').'
                            <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm text-white btn-danger mr-1"><i class="fas fa-trash"></i></button>
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
