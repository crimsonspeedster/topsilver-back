<?php
namespace App\Services;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class EnvironmentPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getEnvPrefix() . '/'
            . $this->getModelFolder($media) . '/'
            . $media->model_id . '/'
            . $media->collection_name . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive-images/';
    }

    private function getModelFolder(Media $media): string
    {
        return Str::snake(class_basename($media->model_type));
    }

    private function getEnvPrefix(): string
    {
        return match (app()->environment()) {
            'production' => 'production',
            'staging' => 'staging',
            default => 'local',
        };
    }
}
