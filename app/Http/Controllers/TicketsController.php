<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Ticket;
use App\Models\TicketBlacklist;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\Ticket\Admin\AdminCreateNotification;
use App\Notifications\Ticket\Admin\AdminReplyNotification;
use App\Notifications\Ticket\User\CreateNotification;
use App\Settings\GeneralSettings;
use App\Settings\LocaleSettings;
use App\Settings\PterodactylSettings;
use App\Settings\TicketSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class TicketsController extends Controller
{
    const READ_PERMISSION = 'user.ticket.read';
    const WRITE_PERMISSION = 'user.ticket.write';
    public function index(LocaleSettings $locale_settings, TicketSettings $ticketSettings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);
        return view('ticket.index', [
            'ticketsettings' => $ticketSettings,
            'tickets' => Ticket::where('user_id', Auth::user()->id)->paginate(10),
            'ticketcategories' => TicketCategory::all(),
            'locale_datatables' => $locale_settings->datatables
        ]);
    }


    public function store(Request $request, GeneralSettings $generalSettings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        if (RateLimiter::tooManyAttempts('ticket-send:'.Auth::user()->id, $perMinute = 1)) {
            return redirect()->back()->with('error', 'Please wait '. RateLimiter::availableIn('ticket-send:'.Auth::user()->id).' seconds before creating a new Ticket');
        }

        $validateData = [
            'title' => 'required|string|max:255',
            'ticketcategory' => 'required|numeric',
            'priority' => ['required', 'in:Low,Medium,High'],
            'message' => 'required|string|min:10|max:2000',
        ];

        if ($generalSettings->recaptcha_version) {
            switch ($generalSettings->recaptcha_version) {
                case "v2":
                    $validateData['g-recaptcha-response'] = ['required', 'recaptcha'];
                    break;
                case "v3":
                    $validateData['g-recaptcha-response'] = ['required', 'recaptchav3:recaptchathree,0.5'];
                    break;
            }
        }

        $validator = Validator::make($request->all(), $validateData);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $ticket = new Ticket(
            [
                'title' => $request->input('title'),
                'user_id' => Auth::user()->id,
                'ticket_id' => strtoupper(Str::random(8)),
                'ticketcategory_id' => $request->input('ticketcategory'),
                'priority' => $request->input('priority'),
                'message' => $request->input('message'),
                'status' => 'Open',
                'server' => $request->input('server'),
            ]
        );
        $ticket->save();
        $user = Auth::user();

        $staffNotify = User::permission('admin.tickets.get_notification')->get();
        foreach($staffNotify as $staff){
            Notification::send($staff, new AdminCreateNotification($ticket, $user));
        }


        $user->notify(new CreateNotification($ticket));
        RateLimiter::increment('ticket-send:'.Auth::user()->id);

        return redirect()->route('ticket.index')->with('success', __('A ticket has been opened, ID: #') . $ticket->ticket_id);
    }

    public function show($ticket_id, PterodactylSettings $ptero_settings)
    {
        $this->checkPermission(self::READ_PERMISSION);
        try {
            $ticket = Ticket::where('ticket_id', $ticket_id)->firstOrFail();
            if($ticket->user_id != Auth::user()->id){ return redirect()->back()->with('error', __('This ticket is not made by you or dosent exist')); }
        } catch (Exception $e) {
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }
        $ticketcomments = $ticket->ticketcomments;
        $ticketcategory = $ticket->ticketcategory;
        $server = Server::where('id', $ticket->server)->first();
        $pterodactyl_url = $ptero_settings->panel_url;

        return view('ticket.show', compact('ticket', 'ticketcategory', 'ticketcomments', 'server', 'pterodactyl_url'));
    }

    public function reply(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        if (RateLimiter::tooManyAttempts('ticket-reply:'.Auth::user()->id, $perMinute = 2)) {
            return redirect()->back()->with('error', 'Please wait '. RateLimiter::availableIn('ticket-reply:'.Auth::user()->id).' seconds before answering the next Ticket');
        }

        //check in blacklist
        $check = TicketBlacklist::where('user_id', Auth::user()->id)->first();
        if ($check && $check->status == 'True') {
            return redirect()->route('ticket.index')->with('error', __("You can't reply a ticket because you're on the blacklist for a reason: '" . $check->reason . "', please contact the administrator"));
        }
        $this->validate($request, ['ticketcomment' => 'required']);
        try {
            $ticket = Ticket::where('id', $request->input('ticket_id'))->firstOrFail();
            if($ticket->user_id != Auth::user()->id){ return redirect()->back()->with('error', __('This ticket is not made by you or dosent exist')); }
        } catch (Exception $e) {
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }
        $ticket->status = 'Client Reply';
        $ticket->updated_at = now();
        $ticket->update();
        $ticketcomment = TicketComment::create([
            'ticket_id' => $request->input('ticket_id'),
            'user_id' => Auth::user()->id,
            'ticketcomment' => $request->input('ticketcomment'),
            'message' => $request->input('message'),
        ]);
        $user = Auth::user();
        $newmessage = $request->input('ticketcomment');

        $staffNotify = User::permission('admin.tickets.get_notification')->get();
        foreach($staffNotify as $staff){
            Notification::send($staff, new AdminReplyNotification($ticket, $user, $newmessage));
        }
        RateLimiter::increment('ticket-reply:'.Auth::user()->id);
        return redirect()->back()->with('success', __('Your comment has been submitted'));
    }

    public function create()
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        //check in blacklist
        $check = TicketBlacklist::where('user_id', Auth::user()->id)->first();
        if ($check && $check->status == 'True') {
            return redirect()->route('ticket.index')->with('error', __("You can't make a ticket because you're on the blacklist for a reason: '" . $check->reason . "', please contact the administrator"));
        }
        $ticketcategories = TicketCategory::all();
        $servers = Auth::user()->servers;

        return view('ticket.create', compact('ticketcategories', 'servers'));
    }

    public function changeStatus($ticket_id)
    {
        $this->checkPermission(self::WRITE_PERMISSION);


        try {
            $ticket = Ticket::where('user_id', Auth::user()->id)->where("ticket_id", $ticket_id)->firstOrFail();
            if($ticket->user_id != Auth::user()->id){ return redirect()->back()->with('warning', __('This ticket is not made by you or dosent exist')); }
        } catch (Exception $e) {
            return redirect()->back()->with('warning', __('Ticket not found on the server. It potentially got deleted earlier'));
        }
        if ($ticket->status == "Closed") {
            $ticket->status = "Reopened";
            $ticket->save();
            return redirect()->back()->with('success', __('A ticket has been reopened, ID: #') . $ticket->ticket_id);
        }
        $ticket->status = "Closed";
        $ticket->save();
        return redirect()->back()->with('success', __('A ticket has been closed, ID: #') . $ticket->ticket_id);
    }

    public function dataTable()
    {
        $query = Ticket::where('user_id', Auth::user()->id)->get();

        return datatables($query)
            ->addColumn('category', function (Ticket $tickets) {
                return $tickets->ticketcategory->name;
            })
            ->editColumn('title', function (Ticket $tickets) {
                return '<a class="text-info"  href="' . route('ticket.show', ['ticket_id' => $tickets->ticket_id]) . '">' . '#' . $tickets->ticket_id . ' - ' . htmlspecialchars($tickets->title) . '</a>';
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

                return '<span class="badge ' . $badgeColor . '">' . $tickets->status . '</span>';
            })
            ->editColumn('priority', function (Ticket $tickets) {
                return __($tickets->priority);
            })
            ->editColumn('updated_at', function (Ticket $tickets) {
                return [
                    'display' => $tickets->updated_at ? $tickets->updated_at->diffForHumans() : '',
                    'raw' => $tickets->updated_at ? strtotime($tickets->updated_at) : ''
                ];
            })
            ->addColumn('actions', function (Ticket $tickets) {
                $statusButtonColor = ($tickets->status == "Closed") ? 'btn-success' : 'btn-warning';
                $statusButtonIcon = ($tickets->status == "Closed") ? 'fa-redo' : 'fa-times';
                $statusButtonText = ($tickets->status == "Closed") ? __('Reopen') : __('Close');

                return '
                            <a data-content="' . __('View') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('ticket.show', ['ticket_id' => $tickets->ticket_id]) . '" class="btn btn-sm text-white btn-info mr-1"><i class="fas fa-eye"></i></a>
                            <form class="d-inline"  method="post" action="' . route('ticket.changeStatus', ['ticket_id' => $tickets->ticket_id]) . '">
                                ' . csrf_field() . '
                                ' . method_field('POST') . '
                            <button data-content="' . __($statusButtonText) . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm text-white ' . $statusButtonColor . '  mr-1"><i class="fas ' . $statusButtonIcon . '"></i></button>
                            </form>

                            </form>
                ';
            })
            ->rawColumns(['category', 'title', 'status', 'updated_at', "actions"])
            ->make(true);
    }
}
