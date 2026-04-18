<?php

namespace Database\Seeders;

use App\Models\User;
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
            SlugSeeder::class,
            SeoSeeder::class,
            BonusSeeder::class,
            RegionSeeder::class,
            CitySeeder::class,
            ProfileSeeder::class,
            PaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            BundleSeeder::class,
        ]);
    }
}
