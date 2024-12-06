<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Actions;

use Illuminate\Support\Str;

final class ConvertTextResponseToJsonAction
{
    /**
     * @return array<string,mixed>
     */
    public function execute(?string $textResponse): array
    {
        return $this->prepareTextResponse($textResponse);
    }

    /**
     * @return array<string,mixed>
     */
    protected function prepareTextResponse(?string $textResponse): array
    {
        if ( ! $textResponse) {
            return [];
        }
        $string = Str::of($textResponse)
            ->remove('```')
            ->remove('json')
            ->remove("\n")
            ->replace(",\n}", '}')
            ->replaceMatches('/,(\s*\n*[\}\]])/', '$1')
            ->replaceMatches('/,(\s*[\}\]])/', '$1')
            ->replaceMatches('/,(\s*[}\]])/', '$1')
            ->trim()
            ->toString();
        if ( ! json_validate($string)) {
            info('not json ' . json_encode($textResponse));
            apiError()->message('Invalid JSON response received')->send();
        }

        /** @var array<string,mixed> */
        return json_decode($string, true);
    }
}
