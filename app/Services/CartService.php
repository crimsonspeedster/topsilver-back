<?php
namespace App\Services;

use App\Enums\StockStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Certificate;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Pipelines\Discount\Calculator\FinalCalculator;
use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\DiscountPipeline;
use App\Pipelines\Discount\Handlers\BonusHandler;
use App\Pipelines\Discount\Handlers\CertificateHandler;
use App\Pipelines\Discount\Handlers\CouponHandler;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        protected CouponService $couponService,
        protected BonusService $bonusService,
    ) {}

    public function resolveCartItem(Cart $cart, Product $product, ProductVariant|null $variant = null): Builder
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->when(
                $variant,
                fn ($q) => $q->where('product_variant_id', $variant->id),
                fn ($q) => $q->whereNull('product_variant_id')
            );
    }

    public function getAvailableStock(Product $product, ?ProductVariant $variant = null): int
    {
        $max = $product->stock_status === StockStatus::OutOfStock ? 0 : 999;
        $stock = $product->manage_stock
            ? min($product->stock, $max)
            : $max;

        if ($variant) {
            $stock = min($variant->stock, $max);
        }

        return $stock;
    }

    public function loadCartItems(Cart $cart): Cart
    {
        return $cart->fresh([
            'items.product.sluggable',
            'items.variant',
            'coupon',
            'certificates',
        ]);
    }

    /**
     * @throws Exception
     */
    public function ensureCartNotEmpty(Cart $cart): void
    {
        $cart->loadMissing('items');

        if ($cart->items->isEmpty()) {
            throw new Exception('Cart is empty');
        }

        if ($cart->items->sum(fn ($i) => $i->price * $i->quantity) <= 0) {
            throw new Exception('Cart total must be greater than zero');
        }
    }

    /**
     * @throws Exception
     */
    public function addCoupon(Cart $cart, Coupon $coupon): Cart
    {
        $this->ensureCartNotEmpty($cart);
        $this->couponService->validate($coupon);

        if ($cart->coupon_id) {
            throw new Exception('Cart already has a coupon');
        }

        if ($cart->certificates()->exists()) {
            throw new Exception('Cannot combine coupon with certificates');
        }

        $cart->coupon()->associate($coupon);
        $cart->save();

        return $this->recalculateCart($cart);
    }

    public function removeCoupon(Cart $cart): Cart
    {
        if (!$cart->coupon_id) {
            return $cart;
        }

        $cart->coupon()->dissociate();
        $cart->save();

        return $this->recalculateCart($cart);
    }

    /**
     * @throws Exception
     */
    public function addCertificate(Cart $cart, Certificate $certificate): Cart
    {
        $this->ensureCartNotEmpty($cart);

        if ($cart->coupon_id) {
            throw new Exception('Cannot combine certificate with coupon');
        }

        if ($certificate->is_used) {
            throw new Exception('Certificate already used');
        }

        $exists = $cart->certificates()
            ->where('certificate_id', $certificate->id)
            ->exists();

        if ($exists) {
            throw new Exception('Certificate already added to cart');
        }

        if ($cart->total < $certificate->value) {
            throw new Exception("Minimum order amount is {$certificate->value}. Add more items to cart.");
        }

        $cart->certificates()->attach($certificate->id);

        return $this->recalculateCart($cart);
    }

    public function removeCertificate(Cart $cart, Certificate $certificate): Cart
    {
        $cart->certificates()->detach($certificate->id);

        return $this->recalculateCart($cart);
    }

    /**
     * @throws Exception
     */
    public function setBonuses(Cart $cart, User $user, int $amount): Cart
    {
        $this->ensureCartNotEmpty($cart);
        $this->bonusService->validate($user, $amount);

        $max = $cart->subtotal * 0.5;

        if ($amount > $max) {
            throw new Exception('Bonus cannot exceed 50% of cart total');
        }

        $cart->bonuses_used = $amount;
        $cart->save();

        return $this->recalculateCart($cart);
    }

    public function recalculateCart(Cart $cart): Cart
    {
        $cart->loadMissing(['items', 'coupon', 'certificates']);

        $subtotal = $cart->items->sum(fn ($i) => $i->price * $i->quantity);

        $context = new CartDiscountContext(
            cart: $cart,
            subtotal: $subtotal,
        );

        $pipeline = new DiscountPipeline();

        $context = $pipeline->through([
            new CouponHandler(),
            new CertificateHandler(),
            new BonusHandler(),
            new FinalCalculator(),
        ])->send($context);

        return $context->cart;
    }

    public function mergeGuestCartWithUser(string $cartToken, User $user): void
    {
        $guestCart = Cart::where('cart_token', $cartToken)->first();

        if (!$guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'last_modified' => now(),
                'total' => 0,
                'subtotal' => 0,
                'bonuses_used' => 0,
            ]
        );

        $this->mergeCart($guestCart, $userCart);
    }

    public function mergeCart(Cart $guestCart, Cart $userCart): Cart
    {
        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $query = CartItem::query()
                    ->where('cart_id', $userCart->id)
                    ->where('product_id', $guestItem->product_id)
                    ->when(
                        $guestItem->product_variant_id,
                        fn ($q) => $q->where('product_variant_id', $guestItem->product_variant_id),
                        fn ($q) => $q->whereNull('product_variant_id')
                    );

                $existing = $query->first();

                if ($existing) {
                    $existing->quantity += $guestItem->quantity;
                    $existing->save();
                } else {
                    $guestItem->cart_id = $userCart->id;
                    $guestItem->save();
                }
            }

            $this->mergeCoupon($guestCart, $userCart);

            $userCart->certificates()->sync($userCart->certificates->pluck('id'));

            $userCart->bonuses_used = $userCart->bonuses_used ?? 0;
            $userCart->save();

            $this->removeGuestCart($guestCart);
        });

        return $this->recalculateCart($userCart->fresh(['items', 'coupon', 'certificates']));
    }

    private function mergeCoupon(Cart $guestCart, Cart $userCart): void
    {
        $guestCoupon = $guestCart->coupon;
        $userCoupon = $userCart->coupon;

        if (!$guestCoupon) {
            return;
        }

        if ($userCoupon) {
            try {
                $this->couponService->validate($userCoupon);
                return;
            } catch (\Exception $e) {
                $userCart->coupon()->dissociate();
                $userCart->save();
            }
        }

        try {
            $this->couponService->validate($guestCoupon);

            $userCart->coupon()->associate($guestCoupon);
            $userCart->save();
        } catch (\Exception $e) {

        }
    }

    private function clearGuestCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->certificates()->detach();
        $cart->coupon()->dissociate();
        $cart->bonuses_used = 0;

        $cart->save();
    }

    private function removeGuestCart(Cart $cart): void
    {
        $this->clearGuestCart($cart);
        $cart->delete();
    }
}
