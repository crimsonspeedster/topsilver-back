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
        $banner_path = $this->fakeBanner();

        $home_page = Page::where('id', 1)->first();
        $rules_page = Page::where('id', 4)->first();

        if ($home_page) {
            $this->createRelationPageSetting('home_page', $home_page);
        }

        if ($rules_page) {
            $this->createRelationPageSetting('rules_page', $rules_page);
        }

        $this->createTextSetting('np_api_key', config('services.nova_poshta_key'));
        $this->createImageSetting('logo', $logo_path);
        $this->createImageSetting('watermark_image', $logo_path);
        $this->createImageSetting('checkout_banner', $banner_path);
        $this->createImageSetting('cart_banner', $banner_path);
        $this->createNumberSetting('free_shipping', 5000);
        $this->createTextSetting('top_banner_text', fake()->sentence());
        $this->createTextSetting('subscribe_text', fake()->text());
        $this->createTextSetting('delivery_and_return', fake()->realText(2000));
        $this->createTextSetting('size_guide', fake()->realText(1500));
        $this->createSocialLinksSetting('social_links', 4);
        $this->createContactSetting('contacts', 4);
        $this->createBoolSetting('show_watermark', true);
        $this->createProductAdvantagesSetting('product_advantages');
        $this->createSeoRobotsSetting();
    }

    public function createNumberSetting(string $key, int $number): void
    {
        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $number
            ],
            'type' => 'number',
        ]);
    }

    public function createBoolSetting(string $key, bool $bool): void
    {
        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $bool
            ],
            'type' => 'boolean',
        ]);
    }

    private function createImageSetting (string $key, string $image_path): void
    {
        Setting::create([
            'key' => $key,
            'value' => [
                'data' => $image_path,
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
        $model->loadMissing(['sluggable']);

        Setting::create([
            'key' => $key,
            'value' => [
                'data' => [
                    'model_id' => $model->getKey(),
                    'model_slug' => $model->sluggable?->slug,
                ],
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
                'data' => [$advantages],
            ],
            'type' => 'product_advantages',
        ]);
    }

    private function createContactSetting (string $key, int $amount): void
    {
        $contacts = [];

        for ($i = 0; $i < $amount; $i++) {
            $image_path = $this->fakeContactImage();

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

    private function createSocialLinksSetting (string $key, int $amount): void
    {
        $links = [];

        for ($i = 0; $i < $amount; $i++) {
            $image_path = $this->fakeSocialImage();

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

    public function fakeSocialImage(): string
    {
        $images = [
            'resources/src/img/social_1.png',
            'resources/src/img/social_2.png',
            'resources/src/img/social_3.png',
            'resources/src/img/social_4.png',
            'resources/src/img/social_5.png',
        ];

        $source = base_path(fake()->randomElement($images));
        $fileName = 'settings/' . Str::uuid() . '.png';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }

    public function fakeContactImage(): string
    {
        $images = [
            'resources/src/img/contact_1.png',
            'resources/src/img/contact_2.png',
            'resources/src/img/contact_3.png',
        ];

        $source = base_path(fake()->randomElement($images));
        $fileName = 'settings/' . Str::uuid() . '.png';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }

    private function fakeBanner(): string
    {
        $images = [
            'resources/src/img/banner_header.jpg',
        ];

        $randomImage = fake()->randomElement($images);
        $source = base_path($randomImage);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $fileName = 'settings/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }

    private function fakeLogo(): string
    {
        $source = resource_path('src/img/logo.png');
        $fileName = 'settings/' . Str::uuid() . '.png';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }
}
