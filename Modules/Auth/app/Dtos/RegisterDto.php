<?php

declare(strict_types=1);

namespace Modules\Auth\app\Dtos;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCastable;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class RegisterDto extends BaseDto
{
    public function __construct(
        public string $name,
        #[WithCastable(Email::class)]
        public Email $email,
        public string $password,
        #[WithCastable(Phone::class)]
        public ?Phone $phone
    ) {}

    public static function fromRegisterRequest(RegisterRequest $request): self
    {
        return self::from($request->validated());
    }
}
