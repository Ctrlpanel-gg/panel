<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaypalProduct;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class PaypalProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(Request $request)
    {
        $isPaypalSetup = false;
        if (env('PAYPAL_SECRET') && env('PAYPAL_CLIENT_ID')) $isPaypalSetup = true;

        return view('admin.store.index' , [
            'isPaypalSetup' => $isPaypalSetup
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
        PaypalProduct::create(array_merge($request->all(), ['disabled' => $disabled]));

        return redirect()->route('admin.store.index')->with('success', 'store item has been created!');
    }

    /**
     * Display the specified resource.
     *
     * @param PaypalProduct $paypalProduct
     * @return Response
     */
    public function show(PaypalProduct $paypalProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param PaypalProduct $paypalProduct
     * @return Application|Factory|View|Response
     */
    public function edit(PaypalProduct $paypalProduct)
    {
        return view('admin.store.edit', [
            'currencyCodes' => config('currency_codes'),
            'paypalProduct' => $paypalProduct
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param PaypalProduct $paypalProduct
     * @return RedirectResponse
     */
    public function update(Request $request, PaypalProduct $paypalProduct)
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
        $paypalProduct->update(array_merge($request->all(), ['disabled' => $disabled]));

        return redirect()->route('admin.store.index')->with('success', 'store item has been updated!');
    }

    /**
     * @param Request $request
     * @param PaypalProduct $paypalProduct
     * @return RedirectResponse
     */
    public function disable(Request $request, PaypalProduct $paypalProduct)
    {
        $paypalProduct->update(['disabled' => !$paypalProduct->disabled]);

        return redirect()->route('admin.store.index')->with('success', 'product has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PaypalProduct $paypalProduct
     * @return RedirectResponse
     */
    public function destroy(PaypalProduct $paypalProduct)
    {
        $paypalProduct->delete();
        return redirect()->back()->with('success', 'store item has been removed!');
    }


    public function dataTable()
    {
        $query = PaypalProduct::query();

        return datatables($query)
            ->addColumn('actions', function (PaypalProduct $paypalProduct) {
                return '
                            <a data-content="Edit" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.store.edit', $paypalProduct->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>

                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.store.destroy', $paypalProduct->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                           <button data-content="Delete" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->addColumn('disabled', function (PaypalProduct $paypalProduct) {
                $checked = $paypalProduct->disabled == false ? "checked" : "";
                return '
                                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.store.disable', $paypalProduct->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("PATCH") . '
                            <div class="custom-control custom-switch">
                            <input ' . $checked . ' name="disabled" onchange="this.form.submit()" type="checkbox" class="custom-control-input" id="switch' . $paypalProduct->id . '">
                            <label class="custom-control-label" for="switch' . $paypalProduct->id . '"></label>
                          </div>
                       </form>
                ';
            })
            ->editColumn('created_at', function (PaypalProduct $paypalProduct) {
                return $paypalProduct->created_at ? $paypalProduct->created_at->diffForHumans() : '';
            })
            ->editColumn('price', function (PaypalProduct $paypalProduct) {
                return $paypalProduct->formatCurrency();
            })
            ->rawColumns(['actions', 'disabled'])
            ->make();
    }
}
