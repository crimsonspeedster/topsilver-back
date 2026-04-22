<?php

namespace Database\Seeders;

use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rootReviews = collect();
        $users = User::with([
            'orders.items.product'
        ])
            ->where('role', 'customer')
            ->get();

        foreach ($users as $user) {
            foreach ($user->orders as $order) {
                foreach ($order->items as $item) {
                    if (!$item->product_id) {
                        continue;
                    }

                    $alreadyReviewed = ProductReview::where('product_id', $item->product_id)
                        ->where('user_id', $user->id)
                        ->whereNull('parent_id')
                        ->exists();

                    if ($alreadyReviewed) {
                        continue;
                    }

                    if (rand(1, 100) > 30) {
                        continue;
                    }

                    $review = ProductReview::factory()->create([
                        'product_id' => $item->product_id,
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'parent_id' => null,
                    ]);

                    $rootReviews->push($review);
                }
            }
        }

        foreach ($rootReviews as $parent) {
            $replyCount = rand(0, 3);

            for ($i = 0; $i < $replyCount; $i++) {
                $ownerUser = $users->firstWhere('id', $parent->user_id);

                if (! $ownerUser) {
                    continue;
                }

                ProductReview::factory()->create([
                    'product_id' => $parent->product_id,
                    'user_id' => $ownerUser->id,
                    'order_id' => null,
                    'parent_id' => $parent->id,
                    'rating' => null,
                ]);
            }
        }
    }
}
