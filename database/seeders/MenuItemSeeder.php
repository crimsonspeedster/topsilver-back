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
        $menus = Menu::pluck('id')->toArray();

        foreach ($menus as $menuId) {
            MenuItem::factory()->count(rand(3, 7))->create([
                'menu_id' => $menuId,
            ]);
        }
    }
}
