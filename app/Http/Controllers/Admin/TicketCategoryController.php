<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Settings\LocaleSettings;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    const READ_PERMISSION = "admin.tickets.category.read";
    const WRITE_PERMISSION = "admin.tickets.category.write";
    
    public function index(LocaleSettings $locale_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);

        $categories = TicketCategory::all();
        return view('admin.ticket.category')->with([
            "categories" => $categories,
            "locale_datatables" => $locale_settings->datatables
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $request->validate([
            'name' => 'required|string|max:191',
        ]);

        TicketCategory::create($request->all());


        return redirect(route("admin.ticket.category.index"))->with("success",__("Category created"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $request->validate([
            'category' => 'required|int',
            'name' => 'required|string|max:191',
        ]);

        $category = TicketCategory::where("id",$request->category)->firstOrFail();

        $category->name = $request->name;
        $category->save();

        return redirect()->back()->with("success",__("Category name updated"));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $category = TicketCategory::where("id",$id)->firstOrFail();

        if($category->id == 5 ){ //cannot delete "other" category
            return back()->with("error","You cannot delete that category");
        }

        $tickets = Ticket::where("ticketcategory_id",$category->id)->get();

        foreach($tickets as $ticket){
            $ticket->ticketcategory_id = "5";
            $ticket->save();
        }

        $category->delete();

        return redirect()
            ->route('admin.ticket.category.index')
            ->with('success', __('Category removed'));
    }

    public function datatable()
    {
        $query = TicketCategory::withCount("tickets");

        return datatables($query)
            ->addColumn('name', function (TicketCategory $category) {
                return '<span class="flex items-center"><i class="mr-2 fas fa-tag text-zinc-400"></i>' . $category->name . '</span>';
            })
            ->editColumn('tickets', function (TicketCategory $category) {
                return '<span class="flex items-center"><i class="mr-2 fas fa-ticket-alt text-primary-400"></i>' . $category->tickets_count . '</span>';
            })
            ->addColumn('actions', function (TicketCategory $category) {
                if ($category->id == 5) {
                    return '<button disabled data-content="' . __('Cannot Delete Default Category, idiot') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="action-btn disabled opacity-50"><i class="fas fa-trash"></i></button>';
                }
                
                return '<form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.ticket.category.destroy', $category->id) . '">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button data-content="' . __('Delete') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="action-btn danger"><i class="fas fa-trash"></i></button>
                </form>';
            })
            ->editColumn('created_at', function (TicketCategory $category) {
                return $category->created_at ? $category->created_at->diffForHumans() : '';
            })
            ->rawColumns(['name', 'tickets', 'actions'])
            ->make();
    }
}
