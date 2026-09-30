<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Resolves the photo shown for a menu item.
 *
 * Known dishes use the curated photos in public/assets/imgs (the same ones the
 * customer menu page in resources/views/home/blog.blade.php shows); anything
 * else falls back to the file uploaded through the admin panel in public/food_img.
 */
class MenuImages
{
    private const MENU_IMAGES = [
        'chicken-burger' => 'chicken-burger-pink-v8.png',
        'egg-bunwich' => 'egg-bunwich-pink-v8.png',
        'cheesy-chicken' => 'cheesy-chicken-pink-v8.png',
        'cheesy-chicken-hotdog-sandwich' => 'cheesy-chicken-pink-v8.png',
        'cheesy-chicken-hotdog' => 'cheesy-chicken-pink-v8.png',
        'fries' => 'fries-pink-v8.png',
        'classic-fries' => 'fries-pink-v8.png',
        'ice-cream' => 'ice-cream-pink-v8.png',
        'mi-cusina-ice-cream' => 'ice-cream-pink-v8.png',
        'creamy-carbonara' => 'creamy-carbonara-pink-v8.png',
        'classic-spaghetti' => 'classic-spaghetti-pink-v8.png',
        'chicken-nugget' => 'chicken-nugget-pink-v8.png',
        'chicken-nuggets' => 'chicken-nugget-pink-v8.png',
        'chicken-nuggets-rice-bowl' => 'chicken-nugget-pink-v8.png',
        '2-pcs-chicken-meal' => '2-pcs-chicken-meal-pink-v8.png',
        '2-pc-chicken-meal' => '2-pcs-chicken-meal-pink-v8.png',
        '1-pc-chicken-meal' => '1-pc-chicken-meal-pink-v8.png',
        'chicken-spaghetti' => 'chicken-spaghetti-pink-v8.png',
        'chicken-adobo-bunwich' => 'chicken-adobo-bunwich-pink-v8.png',
        'chicken-fillet' => 'chicken-fillet-pink-v8.png',
        'chicken-burger-spaghetti' => 'chicken-burger-spaghetti-pink-v8.png',
        'chicken-adobo-flakes' => 'chicken-adobo-flakes-pink-v8.png',
        'chicken-teriyaki' => 'chicken-teriyaki-pink-v8.png',
        'ham-bowl' => 'ham-bowl-pink-v8.png',
        'siomai' => 'siomai-pink-v8.png',
        'siomai-egg-rice-bowl' => 'siomai-egg-pink-v8.png',
    ];

    /** Absolute URL of the photo for a menu item, or null when it has none. */
    public static function url(?string $title, ?string $image): ?string
    {
        $menuImage = self::MENU_IMAGES[self::slug((string) $title)] ?? null;

        if ($menuImage !== null && is_file(public_path('assets/imgs/'.$menuImage))) {
            return asset('assets/imgs/'.$menuImage);
        }

        return self::uploadUrl($image);
    }

    private static function slug(string $title): string
    {
        return Str::of($title)
            ->slug()
            ->replace('chessy', 'cheesy')
            ->replace('bundwich', 'bunwich')
            ->replace('tereyaki', 'teriyaki')
            ->toString();
    }

    private static function uploadUrl(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $image) === 1) {
            return $image;
        }

        $path = ltrim(str_replace('\\', '/', $image), '/');

        return asset(str_starts_with($path, 'food_img/') ? $path : 'food_img/'.$path);
    }
}
