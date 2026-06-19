<?php
namespace App\Interfaces;

interface HasMeta
{
    public function getSeoTitle(): string;

    public function getSeoDescription(): ?string;
}
