<?php
namespace App\Services;

use Illuminate\Support\Str;

class SlugGenerateService
{
    public function generateMany(
        array $titles,
        array &$usedSlugs
    ): array {
        return array_map(function ($title) use ($usedSlugs) {
            return $this->generate(
                $title,
                $usedSlugs
            );
        }, $titles);
    }

    public function generate(
        string $source,
        array &$usedSlugs
    ): string {
        $baseSlug = Str::slug($source);

        if ($baseSlug === '') {
            $baseSlug = 'item';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (isset($usedSlugs[$slug])) {
            $counter++;

            $slug = "{$baseSlug}-{$counter}";
        }

        $usedSlugs[$slug] = true;

        return $slug;
    }
}
