<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use App\Settings\GeneralSettings;
use App\Settings\LocaleSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ShopProductController extends Controller
{

    const READ_PERMISSION = 'admin.store.read';
    const WRITE_PERMISSION = 'admin.store.write';
    const DISABLE_PERMISSION = 'admin.store.disable';

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(LocaleSettings $locale_settings, GeneralSettings $general_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);

        $isStoreEnabled = $general_settings->store_enabled;


        return view('admin.store.index', [
            'isStoreEnabled' => $isStoreEnabled,
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create(GeneralSettings $general_settings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.store.create', [
            'currencyCodes' => config('currency_codes'),
            'credits_display_name' => $general_settings->credits_display_name
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
            'disabled' => 'nullable',
            'type' => 'required|string',
            'currency_code' => ['required', 'string', 'max:3', Rule::in(config('currency_codes'))],
            'price' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'quantity' => 'required|numeric',
            'description' => 'required|string|max:60',
            'display' => 'required|string|max:60',
        ]);

        $disabled = !is_null($request->input('disabled'));
        ShopProduct::create(array_merge($request->all(), ['disabled' => $disabled]));

        return redirect()->route('admin.store.index')->with('success', __('Store item has been created!'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ShopProduct  $shopProduct
     * @return Application|Factory|View|Response
     */
    public function edit(ShopProduct $shopProduct, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.store.edit', [
            'currencyCodes' => config('currency_codes'),
            'shopProduct' => $shopProduct,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return RedirectResponse
     */
    public function update(Request $request, ShopProduct $shopProduct)
    {
        $request->validate([
            'disabled' => 'nullable',
            'type' => 'required|string',
            'currency_code' => ['required', 'string', 'max:3', Rule::in(config('currency_codes'))],
            'price' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'quantity' => 'required|numeric|max:100000000',
            'description' => 'required|string|max:60',
            'display' => 'required|string|max:60',
        ]);

        $disabled = !is_null($request->input('disabled'));
        $shopProduct->update(array_merge($request->all(), ['disabled' => $disabled]));

        return redirect()->route('admin.store.index')->with('success', __('Store item has been updated!'));
    }

    /**
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return RedirectResponse
     */
    public function disable(ShopProduct $shopProduct)
    {
        $this->checkPermission(self::DISABLE_PERMISSION);

        $shopProduct->update(['disabled' => !$shopProduct->disabled]);

        return redirect()->route('admin.store.index')->with('success', __('Product has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ShopProduct  $shopProduct
     * @return RedirectResponse
     */
    public function destroy(ShopProduct $shopProduct)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        $shopProduct->delete();

        return redirect()->back()->with('success', __('Store item has been removed!'));
    }

    public function dataTable(Request $request)
    {
        $query = ShopProduct::query();


        return datatables($query)
            ->addColumn('actions', function (ShopProduct $shopProduct) {
                return '
                            <a data-content="' . __('Edit') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.store.edit', $shopProduct->id) . '" class="mr-1 btn btn-sm btn-info"><i class="fas fa-pen"></i></a>

                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.store.destroy', $shopProduct->id) . '">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                           <button data-content="' . __('Delete') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->editColumn('disabled', function (ShopProduct $shopProduct) {
                $checked = $shopProduct->disabled == false ? 'checked' : '';

                return '
                                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.store.disable', $shopProduct->id) . '">
                            ' . csrf_field() . '
                            ' . method_field('PATCH') . '
                            <div class="custom-control custom-switch">
                            <input ' . $checked . ' name="disabled" onchange="this.form.submit()" type="checkbox" class="custom-control-input" id="switch' . $shopProduct->id . '">
                            <label class="custom-control-label" for="switch' . $shopProduct->id . '"></label>
                          </div>
                       </form>
                ';
            })
            ->editColumn('created_at', function (ShopProduct $shopProduct) {
                return $shopProduct->created_at ? $shopProduct->created_at->diffForHumans() : '';
            })
            ->editColumn('price', function (ShopProduct $shopProduct) {
                return $shopProduct->formatToCurrency($shopProduct->price);
            })
            ->rawColumns(['actions', 'disabled'])
            ->make();
    }
}
