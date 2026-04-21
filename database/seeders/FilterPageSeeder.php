<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\FilterPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::pluck('id');

        foreach ($categories as $categoryId) {
            $attributeIds = DB::table('product_filter_index')
                ->where('category_id', $categoryId)
                ->distinct()
                ->pluck('attribute_id');

            if ($attributeIds->isEmpty()) {
                continue;
            }

            $pagesCount = rand(1, 2);

            for ($i = 0; $i < $pagesCount; $i++) {
                $page = FilterPage::factory()->create([
                    'category_id' => $categoryId,
                ]);

                $selectedAttributes = $attributeIds
                    ->shuffle()
                    ->take(rand(1, min(3, $attributeIds->count())));

                foreach ($selectedAttributes as $attributeId) {
                    $termIds = DB::table('product_filter_index')
                        ->where('category_id', $categoryId)
                        ->where('attribute_id', $attributeId)
                        ->distinct()
                        ->pluck('attribute_term_id');

                    if ($termIds->isEmpty()) {
                        continue;
                    }

                    $selectedTerms = $termIds
                        ->shuffle()
                        ->take(rand(1, min(2, $termIds->count())));

                    foreach ($selectedTerms as $termId) {
                        DB::table('filter_page_filters')->insert([
                            'filter_page_id' => $page->id,
                            'attribute_id' => $attributeId,
                            'attribute_term_id' => $termId,
                        ]);
                    }
                }
            }
        }
    }
}
