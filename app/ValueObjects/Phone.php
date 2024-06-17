<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Str;
use JsonSerializable;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber as LaravelPhone;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class Phone implements Castable, JsonSerializable
{
    protected string $value;

    private function __construct(string$value)
    {
        $this->value = Str::of($value)
            ->replace(' ', '')
            ->replace('+', '')
            ->toString();

    }

    public function toNative(): string
    {
        return $this->value;
    }

    public function countryCode(): string
    {
        return Str::substr($this->value, 0, 3);
    }

    public function ISOCode(): string
    {
        return $this->toLaravelPhone();
        //        return $this->toLaravelPhone()->getCountry();
    }

    public function toFormatInternational(): string
    {
        return $this->value;

        //        return $this->toLaravelPhone()->formatInternational();
    }

    public function toLaravelPhone(): string
    {
        return $this->value;

        //        return new LaravelPhone('+' . $this->value);
    }

    public function toListingFormatted(): string
    {
        return $this->value;

        //        return '+' . $this->countryCode()
        //            . ' ' .
        //            str_replace(' ', '', $this->toLaravelPhone()->formatNational());

    }

    public function toFormattedValue(): string
    {
        return $this->value;
        //        try {
        //            return str('+')
        //                ->append($this->countryCode())
        //                ->append(' ')
        //                ->append(
        //                    str($this->toLaravelPhone()->formatNational())
        //                        ->prepend(' ')
        //                        ->replace(' ', '')
        //                        ->ltrim('0')
        //                        ->toString()
        //                )->toString();
        //        } catch (NumberParseException $exception) {
        //            return $this->toNative();
        //        }
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    public function jsonSerialize(): string
    {
        return $this->toFormattedValue();
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
             * @return Phone
             */
            public function cast(
                DataProperty $property,
                mixed $value,
                array $properties,
                CreationContext $context
            ): Phone {
                return Phone::from($value);
            }
        };
    }
}
