<?php

declare(strict_types=1);

namespace Modules\Auth\app\Casts;

use App\ValueObjects\Phone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class PhoneCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Phone::from($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ( ! $value) {
            return null;
        }
        if ( ! $value instanceof Phone) {
            throw new InvalidArgumentException('The given value is not an App\ValueObjects\Phone instance.');
        }

        return $value->toNative();
    }
}
