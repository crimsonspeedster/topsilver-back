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

        while (in_array($slug, $usedSlugs)) {
            $counter++;

            $slug = "{$baseSlug}-{$counter}";
        }

        return $slug;
    }
}
