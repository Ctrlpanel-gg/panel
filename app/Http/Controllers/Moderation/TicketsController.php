<?php

namespace App\Http\Controllers\Moderation;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Server;
use App\Models\TicketCategory;
use App\Models\TicketComment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\Ticket\User\ReplyNotification;

class TicketsController extends Controller
{
    public function index() {
        $tickets = Ticket::orderBy('id','desc')->paginate(10);
        $ticketcategories = TicketCategory::all();
        return view("moderator.ticket.index", compact("tickets", "ticketcategories"));
    }
    public function show($ticket_id) {
        $ticket = Ticket::where("ticket_id", $ticket_id)->firstOrFail();
        $ticketcomments = $ticket->ticketcomments;
        $ticketcategory = $ticket->ticketcategory;
        $server = Server::where('id', $ticket->server)->first();
        return view("moderator.ticket.show", compact("ticket", "ticketcategory", "ticketcomments", "server"));
    }

    public function close($ticket_id) {
        $ticket = Ticket::where("ticket_id", $ticket_id)->firstOrFail();
        $ticket->status = "Closed";
        $ticket->save();
        $ticketOwner = $ticket->user;
        return redirect()->back()->with('success', __('A ticket has been closed, ID: #') . $ticket->ticket_id);
    }

    public function delete($ticket_id){
        $ticket = Ticket::where("ticket_id", $ticket_id)->firstOrFail();
        TicketComment::where("ticket_id", $ticket->id)->delete();
        $ticket->delete();
        return redirect()->back()->with('success', __('A ticket has been deleted, ID: #') . $ticket_id);

    }
    public function reply(Request $request) {
        $this->validate($request, array("ticketcomment" => "required"));
        $ticket = Ticket::where('id', $request->input("ticket_id"))->firstOrFail();
        $ticket->status = "Answered";
        $ticket->update();
        $ticketcomment = TicketComment::create(array(
        	"ticket_id" => $request->input("ticket_id"),
        	"user_id" => Auth::user()->id,
        	"ticketcomment" => $request->input("ticketcomment"),
        ));
        $user = User::where('id', $ticket->user_id)->firstOrFail();
        $newmessage = $request->input("ticketcomment");
        $user->notify(new ReplyNotification($ticket, $user, $newmessage));
        return redirect()->back()->with('success', __('Your comment has been submitted'));
    }
}
