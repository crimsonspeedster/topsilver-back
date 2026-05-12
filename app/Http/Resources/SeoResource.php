<?php
namespace App\Http\Resources;

use App\Enums\SeoRobotTypes;
use App\Models\Seo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Seo
 * */

class SeoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'robots' => $this->formatRobots($this->resolved_robots),
        ];
    }

    private function formatRobots(SeoRobotTypes $robots): array
    {
        return match ($robots) {
            SeoRobotTypes::INDEX_FOLLOW => [
                'index' => true,
                'follow' => true,
            ],

            SeoRobotTypes::NOINDEX_NOFOLLOW => [
                'index' => false,
                'follow' => false,
            ],

            SeoRobotTypes::INDEX_NOFOLLOW => [
                'index' => true,
                'follow' => false,
            ],

            SeoRobotTypes::NOINDEX_FOLLOW => [
                'index' => false,
                'follow' => true,
            ],
        };
    }
}
