<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\FilterPage;
use App\Models\Page;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\Slug;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlugSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSlugs(Product::pluck('id'), Product::class, 'product');
        $this->seedSlugs(Category::pluck('id'), Category::class, 'category');
        $this->seedSlugs(Collection::pluck('id'), Collection::class, 'collection');
        $this->seedSlugs(Promotion::pluck('id'), Promotion::class, 'promotion');
        $this->seedSlugs(FilterPage::pluck('id'), FilterPage::class, 'filter_page');
        $this->seedSlugs(Page::pluck('id'), Page::class, 'page');
        $this->seedSlugs(Shop::pluck('id'), Shop::class, 'shop');
    }

    private function seedSlugs($ids, string $type, string $base): void
    {
        $data = [];
        $counter = 0;

        foreach ($ids as $id) {
            $slug = $counter === 0 ? $base : "{$base}-{$counter}";

            $data[] = [
                'entity_id' => $id,
                'entity_type' => $type,
                'slug' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $counter++;
        }

        Slug::insert($data);
    }
}
