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
            UserSeeder::class, // для прода
            AttributeSeeder::class,
            AttributeTermSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            PromotionSeeder::class,
            LabelSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductRelationSeeder::class,
            FilterPageSeeder::class,
            InstagramPostSeeder::class,
            PageSeeder::class,
            BonusSeeder::class,
            RegionSeeder::class,
            CitySeeder::class, // для прода
            ProfileSeeder::class,
            PaymentMethodSeeder::class, // для прода
            ShippingMethodSeeder::class, // для прода
            ShopSeeder::class,
            BundleSeeder::class,
            ProductReviewSeeder::class,
            CouponSeeder::class,
            CertificateSeeder::class,
            SlugSeeder::class,
            SeoSeeder::class,
            SeoBlockSeeder::class,
            LocationSeeder::class, // для прода
            MenuSeeder::class,
            MenuItemSeeder::class,
            SettingsSeeder::class, // для прода
        ]);
    }
}
