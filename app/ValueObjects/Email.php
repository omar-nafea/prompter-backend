<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Str;
use Modules\Auth\app\Exceptions\EmailException;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class Email implements Castable
{
    protected string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
        if ( ! $this->isValid()) {
            throw EmailException::invalid();
        }
    }

    public function toNative(): string
    {
        return $this->value;
    }

    public function isValid(): bool
    {
        return (bool) filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }

    public function domain(): string
    {
        return Str::after($this->value, '@');
    }

    /**
     * @throws EmailException
     */
    public static function from(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    /**
     * @param array<int, mixed> ...$arguments
     */
    public static function dataCastUsing(...$arguments): Cast
    {
        return new class () implements Cast {
            /**
             * @param DataProperty $property
             * @param string $value
             * @param string[] $properties
             * @param CreationContext<BaseData<int,mixed,string>> $context
             * @return Email
             */
            public function cast(
                DataProperty $property,
                mixed $value,
                array $properties,
                CreationContext $context
            ): Email {
                return Email::from($value);
            }
        };
    }
}
