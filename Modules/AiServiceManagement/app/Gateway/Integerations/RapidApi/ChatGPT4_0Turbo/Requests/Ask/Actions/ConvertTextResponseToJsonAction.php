<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0Turbo\Requests\Ask\Actions;

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
            ->replaceMatches('/\s+/', ' ')
            ->remove('\n')
//            ->replace('\"', '"')
            ->trim()
            ->toString();
        $string = mb_convert_encoding($string, 'UTF-8', 'auto');
        $string = preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            fn ($matches) => mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16BE'),
            $string
        );
        //        dd(json_validate($string));
        dd($string);
        if ( ! json_validate($string)) {
            info('not json ' . json_encode($textResponse));
            apiError()->message('Invalid JSON response received')->send();
        }

        /** @var array<string,mixed> */
        return json_decode($string, true);
    }
}
