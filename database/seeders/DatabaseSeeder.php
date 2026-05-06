<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AttributeSeeder::class,
            AttributeTermSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            LabelSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductRelationSeeder::class,
            FilterPageSeeder::class,
            PostSeeder::class,
            PageSeeder::class,
            SlugSeeder::class,
            SeoSeeder::class,
            SeoBlockSeeder::class,
            BonusSeeder::class,
            RegionSeeder::class,
            CitySeeder::class,
            ProfileSeeder::class,
            PaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
            ShopSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            BundleSeeder::class,
            ProductReviewSeeder::class,
            CouponSeeder::class,
            CertificateSeeder::class,
        ]);
    }
}
