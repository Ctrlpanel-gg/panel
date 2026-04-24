<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PaymentResource extends JsonResource
{
    protected CurrencyHelper $currencyHelper;

    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
        $this->currencyHelper = app(CurrencyHelper::class);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'payment_id' => $this->payment_id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->currencyHelper->convertForDisplay($this->amount),
            'price' => $this->currencyHelper->convertForDisplay($this->price),
            'tax_value' => $this->currencyHelper->convertForDisplay($this->tax_value),
            'total_price' => $this->currencyHelper->convertForDisplay($this->total_price),
            'tax_percent' => $this->tax_percent,
            'currency_code' => $this->currency_code,
            'payment_method' => $this->payment_method,
            'shop_item_product_id' => $this->shop_item_product_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
