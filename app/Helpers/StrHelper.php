<?php

declare(strict_types=1);

namespace App\Helpers;

class StrHelper
{
    public static function minify($value, $separator = ''): string
    {
        return preg_replace(
            '/^\s*[\r\n]/m',
            '',
            str_replace("\n", $separator, $value)
        );
    }
}
