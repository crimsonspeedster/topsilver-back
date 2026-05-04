<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\ProductReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ProductReviewService;
use Illuminate\Support\Facades\Gate;

class ReviewsController extends Controller
{
    public function __construct(
        private readonly ProductReviewService $productReviewService,
    ) {}

    public function index(Product $product)
    {
        $reviews = $product->reviews()
            ->where('status', ReviewStatus::APPROVED)
            ->whereNull('parent_id')
            ->with([
                'user.profile',
            ])
            ->withCount([
                'replies as replies_count' => function ($q) {
                    $q->where('status', ReviewStatus::APPROVED);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'data' => [
                'reviews' => ProductReviewResource::collection($reviews->items()),
                'pagination' => new PaginationResource($reviews),
            ],
        ]);
    }

    public function replies(ProductReview $review)
    {
        $reviews = $review->replies()
            ->where('status', ReviewStatus::APPROVED)
            ->with([
                'user.profile',
            ])
            ->withCount([
                'replies as replies_count' => function ($q) {
                    $q->where('status', ReviewStatus::APPROVED);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'data' => [
                'reviews' => ProductReviewResource::collection($reviews->items()),
                'pagination' => new PaginationResource($reviews),
            ],
        ]);
    }

    public function store(Product $product, StoreProductReviewRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if (!empty($data['parent_id'])) {
            $parent = ProductReview::findOrFail($data['parent_id']);

            abort_unless($parent->product_id === $product->id, 404);

            Gate::authorize('reply', $parent);

            $review = ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'parent_id' => $parent->id,
                'comment' => $data['comment'],
                'status' => $this->productReviewService->resolveStatus($user),
            ]);

            return response()->json([
                'data' => new ProductReviewResource($review),
            ], 201);
        }

        Gate::authorize('createForProduct', [ProductReview::class, $product]);

        $orderItem = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', '!=', OrderStatus::CANCELLED);
            })
            ->first();

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $orderItem?->order_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'status' => $this->productReviewService->resolveStatus($user),
        ]);

        return response()->json([
            'data' => new ProductReviewResource($review),
        ], 201);
    }
}
