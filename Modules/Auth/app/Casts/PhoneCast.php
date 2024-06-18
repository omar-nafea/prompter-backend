<?php

declare(strict_types=1);

namespace Modules\Auth\app\Casts;

use App\ValueObjects\Phone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Phone, Phone>
 */
final class PhoneCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @param  ?string  $value
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Phone
    {
        if ( ! $value) {
            return null;
        }
        return Phone::from($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
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
