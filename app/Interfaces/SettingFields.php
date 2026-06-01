<?php
namespace App\Interfaces;

interface SettingFields
{
    public static function type(): string;

    public static function fields(): array;
}
