<?php

namespace Database\Seeders;

use App\Enums\SeoRobotTypes;
use App\Factories\Blocks\Advantages;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logo_path = $this->fakeLogo();
        $fake_image_path = $this->fakeImage();

        $this->createImageSetting('logo', $logo_path);
        $this->createTextSetting('top_banner_text', fake()->sentence());
        $this->createTextSetting('subscribe_text', fake()->text());
        $this->createSocialLinksSetting('social_links', 4, $fake_image_path);
        $this->createContactSetting('contacts', 4, $fake_image_path);
        $this->createRelationPageSetting('home_page', Page::inRandomOrder()->first());
        $this->createProductAdvantagesSetting('product_advantages');
        $this->createSeoRobotsSetting();
    }

    private function createImageSetting (string $key, string $image_path): void
    {
        Setting::create([
            'key' => $key,
            'value' => [
                'data' => Storage::disk('public')->url($image_path),
            ],
            'type' => 'image',
        ]);
    }

    private function createTextSetting (string $key, string $text): void
    {
        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $text,
            ],
            'type' => 'text',
        ]);
    }

    private function createSeoRobotsSetting (): void
    {
        Setting::create([
            'key' => 'seo_robots',
            'value' => [
                'data' => SeoRobotTypes::INDEX_FOLLOW,
            ],
            'type' => 'seo_robots',
        ]);
    }

    private function createRelationPageSetting (string $key, Model $model): void
    {
        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $model->getKey(),
            ],
            'type' => 'relation_page'
        ]);
    }

    private function createProductAdvantagesSetting(string $key): void
    {
        $advantages = Advantages::make();

        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $advantages,
            ],
            'type' => 'product_advantages',
        ]);
    }

    private function createContactSetting (string $key, int $amount, string $image_path): void
    {
        $contacts = [];

        for ($i = 0; $i < $amount; $i++) {
            $type = fake()->randomElement(['text', 'link']);

            if ($type === 'link') {
                $contacts[] = [
                    'key' => Str::uuid()->toString(),
                    'layout' => 'ContactItemLink',
                    'attributes' => [
                        'title' => fake()->sentence(),
                        'link' => fake()->url(),
                        'image' => Storage::disk('public')->url($image_path),
                    ],
                ];
            }
            else {
                $contacts[] = [
                    'key' => Str::uuid()->toString(),
                    'layout' => 'ContactItemText',
                    'attributes' => [
                        'title' => fake()->sentence(),
                        'image' => Storage::disk('public')->url($image_path),
                    ],
                ];
            }
        }

        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $contacts,
            ],
            'type' => 'contacts'
        ]);
    }

    private function createSocialLinksSetting (string $key, int $amount, string $image_path): void
    {
        $links = [];

        for ($i = 0; $i < $amount; $i++) {
            $links[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'SocialLinkItem',
                'attributes' => [
                    'link' => fake()->url(),
                    'image' => Storage::disk('public')->url($image_path),
                ],
            ];
        }

        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $links,
            ],
            'type' => 'social_links'
        ]);
    }

    private function fakeImage(): string
    {
        $source = base_path('resources/src/img/fake.png');
        $fileName = 'settings/' . Str::uuid() . '.png';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }

    private function fakeLogo(): string
    {
        $source = base_path('resources/src/img/fake_logo.svg');
        $fileName = 'settings/' . Str::uuid() . '.svg';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }
}
