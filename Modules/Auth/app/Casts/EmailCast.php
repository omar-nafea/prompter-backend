<?php

declare(strict_types=1);

namespace Modules\Auth\app\Casts;

use App\ValueObjects\Email;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Modules\Auth\app\Exceptions\EmailException;

/**
 * @implements CastsAttributes<Email, Email>
 */
final class EmailCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     * @param  ?string $value
     * @throws EmailException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Email
    {
        if ( ! $value) {
            return null;
        }
        return Email::from($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ( ! $value instanceof Email) {
            throw new InvalidArgumentException('The given value is not an App\ValueObjects\Email instance.');
        }

        return $value->toNative();
    }
}
