<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Certificate;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;

class CartCertificateController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $hash = hash('sha256', $data['code']);

        $certificate = Certificate::where('code_hash', $hash)
            ->where('is_used', false)
            ->where('activated_at', '<=', now())
            ->where('expires_at', '>', now())
            ->firstOrFail();
        $cart = $request->attributes->get('cart');

        try {
            $cart = $this->cartService->addCertificate($cart, $certificate);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            ),
        ]);
    }

    public function destroy(Certificate $certificate, Request $request)
    {
        $cart = $request->attributes->get('cart');
        $cart = $this->cartService->removeCertificate($cart, $certificate);

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            ),
        ]);
    }
}
