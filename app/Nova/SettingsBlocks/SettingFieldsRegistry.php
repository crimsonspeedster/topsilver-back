<?php
namespace App\Nova\SettingsBlocks;

class SettingFieldsRegistry
{
    public static function all(): array
    {
        return [
            ImageFields::class,
            TextFields::class,
            SeoRobotsFields::class,
            ContactFields::class,
            RelationPageFields::class,
            SocialLinkFields::class,
        ];
    }

    public static function resolve(string $type): ?string
    {
        foreach (static::all() as $class) {
            if ($class::type() === $type) {
                return $class;
            }
        }

        return null;
    }
}
