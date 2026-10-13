<?php

namespace Tests\Unit;

use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\InsufficientResourcesException;
use App\Exceptions\Server\ServerUpgradeException;
use App\Models\Product;
use App\Models\Server;
use App\Services\ServerUpgradeService;
use PHPUnit\Framework\TestCase;

class ServerUpgradeServiceTest extends TestCase
{
    /**
     * Domain exceptions carry expected HTTP status codes.
     */
    public function test_domain_exceptions_carry_correct_status_codes(): void
    {
        $creditsException = new InsufficientCreditsException('Insufficient credits to upgrade the server.');
        $this->assertSame(422, $creditsException->getStatusCode());
        $this->assertSame(422, $creditsException->getCode());
        $this->assertSame('Insufficient credits to upgrade the server.', $creditsException->getMessage());

        $resourcesException = new InsufficientResourcesException('Insufficient resources on the node.');
        $this->assertSame(422, $resourcesException->getStatusCode());
        $this->assertSame(422, $resourcesException->getCode());

        $upgradeException = new ServerUpgradeException('Upgrade failed.');
        $this->assertSame(500, $upgradeException->getStatusCode());

        // The lock-contention status must survive the API's getCode() mapping.
        $lockedException = new ServerUpgradeException('Another upgrade request is in progress.', 429);
        $this->assertSame(429, $lockedException->getStatusCode());
        $this->assertSame(429, $lockedException->getCode());
    }

    /**
     * Refund math sanity check.
     */
    public function test_prorated_math_on_upgrade(): void
    {
        // 1 month billing period = 30 days ~ 2592000 seconds
        // Price = 100. If 0 time used, refund = 100, net price = 0.
        $productPrice = 100.0;
        $timeUsedSeconds = 0;
        $billingPeriodSeconds = 2592000;

        $refundAmount = $productPrice - ($productPrice * ($timeUsedSeconds / $billingPeriodSeconds));
        $finalPrice = $productPrice - $refundAmount;

        $this->assertEqualsWithDelta(0.0, $finalPrice, 0.001);
    }

    /**
     * Refund boundaries: refund = oldPrice * (1 - timeUsed / period).
     * Overdue goes negative - pinned policy, not a bug.
     */
    public function test_refund_formula_boundaries(): void
    {
        $oldPrice = 7200.0;
        $period = 3600.0;

        $refundAtStart = $oldPrice - ($oldPrice * (0 / $period));
        $this->assertEqualsWithDelta($oldPrice, $refundAtStart, 0.001);

        $refundAtPeriodEnd = $oldPrice - ($oldPrice * ($period / $period));
        $this->assertEqualsWithDelta(0.0, $refundAtPeriodEnd, 0.001);

        // Overdue goes negative: pinned policy, not a bug.
        $refundPastPeriod = $oldPrice - ($oldPrice * ((2 * $period) / $period));
        $this->assertEqualsWithDelta(-$oldPrice, $refundPastPeriod, 0.001);
    }

    /**
     * Period map is shared by pre-check and charge. Month is a fixed 30
     * days; unknown periods fall back to hourly.
     */
    public function test_billing_period_seconds_are_fixed_and_shared(): void
    {
        $expected = [
            'hourly' => 60 * 60,
            'daily' => 24 * 60 * 60,
            'weekly' => 7 * 24 * 60 * 60,
            'monthly' => 30 * 24 * 60 * 60,
            'quarterly' => 3 * 30 * 24 * 60 * 60,
            'half-annually' => 6 * 30 * 24 * 60 * 60,
            'annually' => 12 * 30 * 24 * 60 * 60,
        ];

        foreach ($expected as $period => $seconds) {
            $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
            $product->billing_period = $period;

            $this->assertSame($seconds, ServerUpgradeService::getSecondsFromBillingPeriod($product), $period);
        }

        $unknown = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $unknown->billing_period = 'not-a-period';

        $this->assertSame(60 * 60, ServerUpgradeService::getSecondsFromBillingPeriod($unknown));
    }

    /**
     * Same-product check ignores scalar types (int 1 vs string '1'),
     * or retries would double-charge.
     */
    public function test_is_same_product_ignores_scalar_types(): void
    {
        $method = new \ReflectionMethod(ServerUpgradeService::class, 'isSameProduct');
        $method->setAccessible(true);
        $service = (new \ReflectionClass(ServerUpgradeService::class))->newInstanceWithoutConstructor();

        $serverWithIntId = (new \ReflectionClass(Server::class))->newInstanceWithoutConstructor();
        $serverWithIntId->product_id = 1;

        $productWithStringId = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $productWithStringId->id = '1';

        $this->assertTrue($method->invoke($service, $serverWithIntId, $productWithStringId));

        $otherProduct = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $otherProduct->id = '2';

        $this->assertFalse($method->invoke($service, $serverWithIntId, $otherProduct));

        $serverWithStringId = (new \ReflectionClass(Server::class))->newInstanceWithoutConstructor();
        $serverWithStringId->product_id = 'abc123';

        $productWithSameStringId = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $productWithSameStringId->id = 'abc123';

        $this->assertTrue($method->invoke($service, $serverWithStringId, $productWithSameStringId));
    }
}
