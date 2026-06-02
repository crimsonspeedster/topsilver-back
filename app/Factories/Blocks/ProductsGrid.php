<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductsGrid implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'ProductsGrid',
            'attributes' => [
                'title' => 'Products grid',
                'description' => 'subtitle',
                'products' => json_encode(
                    fake()->randomElements(
                        Product::pluck('id')->all(),
                        rand(2, 5)
                    )
                ),
            ],
        ];
    }
}
