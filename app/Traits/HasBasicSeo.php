<?php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasBasicSeo
{
    public function getSeoTitle(): ?string
    {
        return $this->title ?? '';
    }

    public function getSeoDescription(): ?string
    {
        return Str::limit(strip_tags($this->description ?? ''), 160);
    }
}
