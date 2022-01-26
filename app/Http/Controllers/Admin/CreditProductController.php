<?php

namespace App\Http\Controllers\Admin;

use App\Models\CreditProduct;
use App\Models\Settings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class CreditProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(Request $request)
    {
        $isPaymentSetup = false;

        if (
            env('APP_ENV') == 'local' ||
            config("SETTINGS::PAYMENTS:PAYPAL:SECRET") && config("SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID") ||
            config("SETTINGS::PAYMENTS:STRIPE:SECRET") && config("SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET") && config("SETTINGS::PAYMENTS:STRIPE:METHODS")
        ) $isPaymentSetup = true;

        return view('admin.store.index', [
            'isPaymentSetup' => $isPaymentSetup
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        return view('admin.store.create', [
            'currencyCodes' => config('currency_codes')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "disabled"      => "nullable",
            "type"          => "required|string",
            "currency_code" => ["required", "string", "max:3", Rule::in(config('currency_codes'))],
            "price"         => "required|regex:/^\d+(\.\d{1,2})?$/",
            "quantity"      => "required|numeric",
            "description"   => "required|string|max:60",
            "display"       => "required|string|max:60",
        ]);

        $disabled = !is_null($request->input('disabled'));
        CreditProduct::create(array_merge($request->all(), ['disabled' => $disabled]));

        return redirect()->route('admin.store.index')->with('success', __('Store item has been created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param CreditProduct $creditProduct
     * @return Response
     */
    public function show(CreditProduct $creditProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CreditProduct $creditProduct
     * @return Application|Factory|View|Response
     */
    public function edit(CreditProduct $creditProduct)
    {
        return view('admin.store.edit', [
            'currencyCodes' => config('currency_codes'),
            'creditProduct' => $creditProduct
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param CreditProduct $creditProduct
     * @return RedirectResponse
     */
    public function update(Request $request, CreditProduct $creditProduct)
    {
        $request->validate([
            "disabled"      => "nullable",
            "type"          => "required|string",
            "currency_code" => ["required", "string", "max:3", Rule::in(config('currency_codes'))],
            "price"         => "required|regex:/^\d+(\.\d{1,2})?$/",
            "quantity"      => "required|numeric|max:100000000",
            "description"   => "required|string|max:60",
            "display"       => "required|string|max:60",
        ]);

        $disabled = !is_null($request->input('disabled'));
        $creditProduct->update(array_merge($request->all(), ['disabled' => $disabled]));

        return redirect()->route('admin.store.index')->with('success', __('Store item has been updated!'));
    }

    /**
     * @param Request $request
     * @param CreditProduct $creditProduct
     * @return RedirectResponse
     */
    public function disable(Request $request, CreditProduct $creditProduct)
    {
        $creditProduct->update(['disabled' => !$creditProduct->disabled]);

        return redirect()->route('admin.store.index')->with('success', __('Product has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CreditProduct $creditProduct
     * @return RedirectResponse
     */
    public function destroy(CreditProduct $creditProduct)
    {
        $creditProduct->delete();
        return redirect()->back()->with('success', __('Store item has been removed!'));
    }


    public function dataTable()
    {
        $query = CreditProduct::query();

        return datatables($query)
            ->addColumn('actions', function (CreditProduct $creditProduct) {
                return '
                            <a data-content="' . __("Edit") . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.store.edit', $creditProduct->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>

                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.store.destroy', $creditProduct->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                           <button data-content="' . __("Delete") . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->addColumn('disabled', function (CreditProduct $creditProduct) {
                $checked = $creditProduct->disabled == false ? "checked" : "";
                return '
                                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.store.disable', $creditProduct->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("PATCH") . '
                            <div class="custom-control custom-switch">
                            <input ' . $checked . ' name="disabled" onchange="this.form.submit()" type="checkbox" class="custom-control-input" id="switch' . $creditProduct->id . '">
                            <label class="custom-control-label" for="switch' . $creditProduct->id . '"></label>
                          </div>
                       </form>
                ';
            })
            ->editColumn('created_at', function (CreditProduct $creditProduct) {
                return $creditProduct->created_at ? $creditProduct->created_at->diffForHumans() : '';
            })
            ->editColumn('price', function (CreditProduct $creditProduct) {
                return $creditProduct->formatToCurrency($creditProduct->price);
            })
            ->rawColumns(['actions', 'disabled'])
            ->make();
    }
}
