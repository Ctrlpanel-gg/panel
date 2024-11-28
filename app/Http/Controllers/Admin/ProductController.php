<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Nest;
use App\Models\Product;
use App\Settings\GeneralSettings;
use App\Settings\LocaleSettings;
use App\Settings\UserSettings;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    const READ_PERMISSION = "admin.products.read";

    const WRITE_PERMISSION = "admin.products.create";
    const EDIT_PERMISSION = "admin.products.edit";
    const DELETE_PERMISSION = "admin.products.delete";
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(LocaleSettings $locale_settings)
    {
        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $this->checkAnyPermission($allConstants);

        return view('admin.products.index', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(GeneralSettings $general_settings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        return view('admin.products.create', [
            'locations' => Location::with('nodes')->get(),
            'nests' => Nest::with('eggs')->get(),
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    public function clone(Product $product, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.products.create', [
            'product' => $product,
            'credits_display_name' =>  $general_settings->credits_display_name,
            'locations' => Location::with('nodes')->get(),
            'nests' => Nest::with('eggs')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:30',
            'price' => 'required|numeric|max:1000000|min:0',
            'memory' => 'required|numeric|max:1000000|min:5',
            'cpu' => 'required|numeric|max:1000000|min:0',
            'swap' => 'required|numeric|max:1000000|min:0',
            'description' => 'required|string|max:191',
            'disk' => 'required|numeric|max:1000000|min:5',
            'minimum_credits' => 'required|numeric|max:1000000|min:-1',
            'io' => 'required|numeric|max:1000000|min:0',
            'serverlimit' => 'required|numeric|max:1000000|min:0',
            'databases' => 'required|numeric|max:1000000|min:0',
            'backups' => 'required|numeric|max:1000000|min:0',
            'allocations' => 'required|numeric|max:1000000|min:0',
            'nodes.*' => 'required|exists:nodes,id',
            'eggs.*' => 'required|exists:eggs,id',
            'disabled' => 'nullable',
            'oom_killer' => 'nullable',
            'billing_period' => 'required|in:hourly,daily,weekly,monthly,quarterly,half-annually,annually',
        ]);


        $disabled = ! is_null($request->input('disabled'));
        $oomkiller = ! is_null($request->input('oom_killer'));
        $product = Product::create(array_merge($request->all(), ['disabled' => $disabled, 'oom_killer' => $oomkiller]));

        //link nodes and eggs
        $product->eggs()->attach($request->input('eggs'));
        $product->nodes()->attach($request->input('nodes'));

        return redirect()->route('admin.products.index')->with('success', __('Product has been created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return Application|Factory|View
     */
    public function show(Product $product, UserSettings $user_settings, GeneralSettings $general_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::WRITE_PERMISSION]);

        return view('admin.products.show', [
            'product' => $product,
            'minimum_credits' => $user_settings->min_credits_to_make_server,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Product  $product
     * @return Application|Factory|View
     */
    public function edit(Product $product, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::EDIT_PERMISSION);

        return view('admin.products.edit', [
            'product' => $product,
            'locations' => Location::with('nodes')->get(),
            'nests' => Nest::with('eggs')->get(),
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Product  $product
     * @return RedirectResponse
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:30',
            'price' => 'required|numeric|max:1000000|min:0',
            'memory' => 'required|numeric|max:1000000|min:5',
            'cpu' => 'required|numeric|max:1000000|min:0',
            'swap' => 'required|numeric|max:1000000|min:0',
            'description' => 'required|string|max:191',
            'disk' => 'required|numeric|max:1000000|min:5',
            'io' => 'required|numeric|max:1000000|min:0',
            'minimum_credits' => 'required|numeric|max:1000000|min:-1',
            'databases' => 'required|numeric|max:1000000|min:0',
            'serverlimit' => 'required|numeric|max:1000000|min:0',
            'backups' => 'required|numeric|max:1000000|min:0',
            'allocations' => 'required|numeric|max:1000000|min:0',
            'nodes.*' => 'required|exists:nodes,id',
            'eggs.*' => 'required|exists:eggs,id',
            'disabled' => 'nullable',
            'oom_killer' => 'nullable',
            'billing_period' => 'required|in:hourly,daily,weekly,monthly,quarterly,half-annually,annually',
        ]);

        $disabled = ! is_null($request->input('disabled'));
        $oomkiller = ! is_null($request->input('oom_killer'));
        $product->update(array_merge($request->all(), ['disabled' => $disabled, 'oom_killer' => $oomkiller]));

        //link nodes and eggs
        $product->eggs()->detach();
        $product->nodes()->detach();
        $product->eggs()->attach($request->input('eggs'));
        $product->nodes()->attach($request->input('nodes'));

        return redirect()->route('admin.products.index')->with('success', __('Product has been updated!'));
    }

    /**
     * @param  Request  $request
     * @param  Product  $product
     * @return RedirectResponse
     */
    public function disable(Product $product)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $product->update(['disabled' => ! $product->disabled]);

        return redirect()->route('admin.products.index')->with('success', 'Product has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Product  $product
     * @return RedirectResponse
     */
    public function destroy(Product $product)
    {
        $this->checkPermission(self::DELETE_PERMISSION);

        $servers = $product->servers()->count();
        if ($servers > 0) {
            return redirect()->back()->with('error', "Product cannot be removed while it's linked to {$servers} servers");
        }

        $product->delete();

        return redirect()->back()->with('success', __('Product has been removed!'));
    }

    /**
     * @return JsonResponse|mixed
     *
     * @throws Exception|Exception
     */
    public function dataTable()
    {
        $query = Product::with(['servers']);

        return datatables($query)
            ->addColumn('actions', function (Product $product) {
                return '
                            <a data-content="'.__('Show').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.products.show', $product->id).'" class="mr-1 text-white btn btn-sm btn-warning"><i class="fas fa-eye"></i></a>
                            <a data-content="'.__('Clone').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.products.clone', $product->id).'" class="mr-1 text-white btn btn-sm btn-primary"><i class="fas fa-clone"></i></a>
                            <a data-content="'.__('Edit').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.products.edit', $product->id).'" class="mr-1 btn btn-sm btn-info"><i class="fas fa-pen"></i></a>

                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.products.destroy', $product->id).'">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                           <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })

            ->addColumn('servers', function (Product $product) {
                return $product->servers()->count();
            })
            ->addColumn('nodes', function (Product $product) {
                return $product->nodes()->count();
            })
            ->addColumn('eggs', function (Product $product) {
                return $product->eggs()->count();
            })
            ->editColumn('disabled', function (Product $product) {
                $checked = $product->disabled == false ? 'checked' : '';

                return '
                    <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.products.disable', $product->id).'">
                        '.csrf_field().'
                        '.method_field('PATCH').'
                        <div class="custom-control custom-switch">
                        <input '.$checked.' name="disabled" onchange="this.form.submit()" type="checkbox" class="custom-control-input" id="switch'.$product->id.'">
                        <label class="custom-control-label" for="switch'.$product->id.'"></label>
                        </div>
                    </form>
                ';
            })
            ->editColumn('minimum_credits', function (Product $product, UserSettings $user_settings) {
                return $product->minimum_credits==-1 ? $user_settings->min_credits_to_make_server : $product->minimum_credits;
            })
            ->editColumn('serverlimit', function (Product $product) {
                return $product->serverlimit == 0 ? "∞" : $product->serverlimit;
            })
            ->editColumn('oom_killer', function (Product $product) {
                return $product->oom_killer ? __("enabled") : __("disabled");
            })
            ->editColumn('created_at', function (Product $product) {
                return $product->created_at ? $product->created_at->diffForHumans() : '';
            })
            ->rawColumns(['actions', 'disabled'])
            ->make();
    }
}
