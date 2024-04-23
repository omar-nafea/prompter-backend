<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Str;
use Modules\Auth\app\Exceptions\EmailException;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class Email implements Castable
{
    protected string $value;

    private function __construct($value)
    {
        $this->value = $value;
        if (! $this->isValid()) {
            throw EmailException::invalid();
        }
    }

    public function toNative(): string
    {
        return $this->value;
    }

    public function isValid()
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }

    public function domain()
    {
        return Str::after($this->value, '@');
    }

    /**
     * @throws EmailException
     */
    public static function from($value)
    {
        return new self($value);
    }

    public function __toString()
    {
        return $this->toNative();
    }

    public static function dataCastUsing(...$arguments): Cast
    {
        return new class() implements Cast
        {
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
            {
                return Email::from($value);
            }
        };
    }
}
