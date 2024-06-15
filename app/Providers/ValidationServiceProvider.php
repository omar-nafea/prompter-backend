<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Validator::extend(
            'alpha_spaces',
            static fn($attribute, $value) => preg_match('/^[\pL\s]+$/u', $value)
        );

        Validator::extend(
            'alpha_spaces_special_chars',
            static fn($attribute, $value) => preg_match('/^[\pL\s\(\-\_\.\/\\,)]+$/u', $value)
        );

        Validator::extend(
            'alpha_spaces_num',
            static fn($attribute, $value) => preg_match('/^[\pL\s\pM\pN]+$/u', $value)
        );

        Validator::extend(
            'arabic_alpha_spaces_num',
            static fn($attribute, $value) => preg_match('/^[\p{Arabic} \pN]+$/u', $value)
        );

        Validator::extend(
            'arabic_alpha_spaces_num_special_chars',
            static fn($attribute, $value) => preg_match('/^[\p{Arabic} \pN\(\-\_\.\/\\,)]+$/u', $value)
        );

        Validator::extend(
            'english_alpha_spaces_num',
            static fn($attribute, $value) => preg_match('/^[a-zA-Z \pN]+$/u', $value)
        );

        Validator::extend(
            'english_alpha_spaces_num_special_chars',
            static fn($attribute, $value) => preg_match('/^[a-zA-Z \pN\(\-\_\.\/\\,)]+$/u', $value)
        );

        Validator::extend(
            'english_alpha_num',
            static fn($attribute, $value) => preg_match('/^[a-zA-Z\pN]+$/u', $value)
        );

        Validator::extend(
            'english_numbers',
            static fn($attribute, $value) => preg_match('/^[+0-9]+$/', $value)
        );

        Validator::extend(
            'arabic_spaces_special_chars',
            static fn($attribute, $value) => preg_match('/^[\p{Arabic} \(\-\_\.\/\\,)]+$/u', $value)
        );

        Validator::extend(
            'english_spaces_special_chars',
            static fn($attribute, $value) => preg_match('/^[a-zA-Z \(\-\_\.\/\\,)]+$/', $value)
        );

        Validator::extend(
            'phone_format',
            static fn($attribute, $value) => preg_match('/^\+[0-9]+$/', $value)
        );

        Validator::extend(
            'map_link_format',
            static fn($attribute, $value) => preg_match(
                '/^https:\/\/(www\.google\.com\/maps|maps\.app\.goo\.gl|maps\.google\.com)(\/.+)$/',
                $value
            )
        );

    }
}
