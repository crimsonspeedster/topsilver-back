<?php
namespace App\Services;

use App\Enums\SeoRobotTypes;
use Illuminate\Support\Str;

class SeoGenerateService
{
    public function generateSeo(string $title, ?string $description, ?string $keywords): array
    {
        $text = $this->resolveDescription($title, $description);

        return [
            'title' => Str::limit(strip_tags($title), 60),
            'description' => $text,
            'keywords' => $keywords ? $this->normalizeKeywords($keywords) : null,
            'robots' => SeoRobotTypes::INDEX_FOLLOW,
        ];
    }

    private function resolveDescription(string $title, ?string $description): string
    {
        if ($description) {
            return Str::limit(strip_tags($description), 160);
        }

        return Str::limit(strip_tags($title), 160);
    }

    private function normalizeKeywords(string $keywords): string
    {
        return collect(explode(',', $keywords))
            ->map(fn ($k) => trim($k))
            ->filter()
            ->unique()
            ->implode(', ');
    }
}
