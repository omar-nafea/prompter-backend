<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Str;
use JsonSerializable;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber as LaravelPhone;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class Phone implements Castable, JsonSerializable
{
    protected string $value;

    private function __construct($value)
    {
        $this->value = str($value)->replace(' ', '')->replace('+', '')->toString();

    }

    public function toNative(): string
    {
        return $this->value;
    }

    public function countryCode()
    {
        return Str::substr($this->value, 0, 3);
    }

    public function ISOCode()
    {
        return $this->toLaravelPhone()->getCountry();
    }

    public function toFormatInternational()
    {
        return $this->value;

        return $this->toLaravelPhone()->formatInternational();
    }

    public function toLaravelPhone()
    {
        return $this->value;

        return new LaravelPhone('+' . $this->value);
    }

    public function toListingFormatted()
    {
        return $this->value;

        return '+' . $this->countryCode()
            . ' ' .
            str_replace(' ', '', $this->toLaravelPhone()->formatNational());

    }

    public function toFormattedValue(): string
    {
        return $this->value;
        try {
            return str('+')
                ->append($this->countryCode())
                ->append(' ')
                ->append(
                    str($this->toLaravelPhone()->formatNational())
                        ->prepend(' ')
                        ->replace(' ', '')
                        ->ltrim('0')
                        ->toString()
                )->toString();
        } catch (NumberParseException $exception) {
            return $this->toNative();
        }
    }

    public static function from($value)
    {
        return new self($value);
    }

    public function __toString()
    {
        return $this->toNative();
    }

    public function jsonSerialize(): string
    {
        return $this->toFormattedValue();
    }

    public static function dataCastUsing(...$arguments): Cast
    {
        //        dd(  new class implements Cast {
        //        public function cast(DataProperty $property, mixed $value, array $context): mixed {
        //            return new self($value);
        //        }
        //    });
        return new class() implements Cast
        {
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
            {
                return Phone::from($value);
            }
        };
    }
}
