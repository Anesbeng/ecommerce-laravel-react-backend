<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Chargily\ChargilyPay\Auth\Credentials;
use Chargily\ChargilyPay\ChargilyPay;
use Illuminate\Http\Request;
use ReflectionClass;
use Exception;

class PaymentController extends Controller
{
    protected ChargilyPay $chargily;

    public function __construct()
    {
        $credentials = new Credentials([
            "mode"   => "test",
            "public" => env('CHARGILY_PUBLIC_KEY'),
            "secret" => env('CHARGILY_SECRET_KEY'),
        ]);
        $this->chargily = new ChargilyPay($credentials);
    }

    /**
     * The Chargily SDK stores real data inside a protected "attributes"
     * array on every Element object (ProductElement, PriceElement,
     * CheckoutElement, etc.), and its own magic __get() doesn't
     * reliably expose keys like "id". Reading it directly via
     * reflection sidesteps that SDK quirk entirely.
     *
     * If the requested key doesn't exist, this throws a clear error
     * listing the real available keys — instead of a silent null or a
     * cryptic "undefined property" crash — so a wrong guess is easy to
     * fix in one step rather than another blind debugging round.
     */
    private function attr($element, string $key)
    {
        $reflection = new ReflectionClass($element);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($element);

        if (!array_key_exists($key, $attributes)) {
            throw new Exception(
                "Chargily element ".get_class($element)." has no '{$key}' attribute. ".
                "Available keys: ".implode(', ', array_keys($attributes))
            );
        }

        return $attributes[$key];
    }

    public function initiate($orderId)
    {
        $order = Order::findOrFail($orderId);

        $product = $this->chargily->products()->create([
            "name" => "Pure Wear Order #{$order->id}",
        ]);
        $productId = $this->attr($product, 'id');

        $price = $this->chargily->prices()->create([
            "product_id" => $productId,
            "amount"     => (int) round($order->grand_total * 100),
            "currency"   => "dzd",
        ]);
        $priceId = $this->attr($price, 'id');

        $checkout = $this->chargily->checkouts()->create([
            "locale"           => "en",
            "description"      => "Pure Wear Order #{$order->id}",
            "items"            => [
                ["price" => $priceId, "quantity" => 1],
            ],
            "success_url"      => "http://localhost:5173/payment-success?order_id={$order->id}",
            "failure_url"      => "http://localhost:5173/payment-failed?order_id={$order->id}",
            "webhook_endpoint" => env('CHARGILY_WEBHOOK_URL'),
        ]);

        $checkoutId  = $this->attr($checkout, 'id');
        $checkoutUrl = $this->attr($checkout, 'url');

        $order->update(['chargily_checkout_id' => $checkoutId]);

        return response()->json(['checkout_url' => $checkoutUrl]);
    }
}