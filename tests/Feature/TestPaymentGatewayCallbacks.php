<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestPaymentGatewayCallbacks extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function paypal_failed_capture_redirects_with_error_without_deleting_the_payment()
    {
        Http::fake([
            'https://api-m.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
            ], 200),
            'https://api-m.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'name' => 'UNPROCESSABLE_ENTITY',
            ], 422),
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test-access-token',
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'name' => 'UNPROCESSABLE_ENTITY',
            ], 422),
        ]);

        $user = User::factory()->create();
        $payment = $this->createPaymentForUser($user, 'PayPal');
        $callbackUrl = URL::temporarySignedRoute('payment.PayPalSuccess', now()->addMinutes(5), [
            'payment' => $payment->id,
        ]);

        $response = $this->get($callbackUrl . '&token=ORDER-123');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::OPEN->value,
        ]);
    }

    #[Test]
    public function stripe_callback_without_session_id_redirects_with_error()
    {
        $user = User::factory()->create();
        $payment = $this->createPaymentForUser($user, 'Stripe');

        $response = $this->get(URL::temporarySignedRoute('payment.StripeSuccess', now()->addMinutes(5), [
            'payment' => $payment->id,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::OPEN->value,
        ]);
    }

    private function createPaymentForUser(User $user, string $paymentMethod): Payment
    {
        $shopProduct = ShopProduct::create([
            'type' => 'Credits',
            'price' => 1000,
            'description' => 'Test product',
            'display' => 'Test credits',
            'currency_code' => 'USD',
            'quantity' => 1000,
            'disabled' => false,
        ]);

        return Payment::create([
            'user_id' => $user->id,
            'payment_id' => null,
            'payment_method' => $paymentMethod,
            'status' => PaymentStatus::OPEN->value,
            'type' => $shopProduct->type,
            'amount' => $shopProduct->quantity,
            'price' => $shopProduct->price,
            'tax_value' => 0,
            'tax_percent' => 0,
            'total_price' => $shopProduct->price,
            'currency_code' => $shopProduct->currency_code,
            'shop_item_product_id' => $shopProduct->id,
        ]);
    }
}
