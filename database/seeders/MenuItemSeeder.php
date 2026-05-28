<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::all();

        foreach ($menus as $menu) {
            $parents = MenuItem::factory()
                ->count(rand(3, 7))
                ->create([
                    'menu_id' => $menu->id,
                    'parent_id' => null,
                ]);

            foreach ($parents as $parent) {
                MenuItem::factory()
                    ->count(rand(2, 5))
                    ->create([
                        'menu_id' => null,
                        'parent_id' => $parent->id,
                    ]);
            }
        }
    }
}
