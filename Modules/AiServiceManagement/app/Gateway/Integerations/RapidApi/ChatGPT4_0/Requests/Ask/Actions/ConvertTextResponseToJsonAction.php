<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\Requests\Ask\Actions;

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
            ->between('```json', '```')
            ->trim()
            ->toString();
        if ( ! json_validate($string)) {
            //todo add exception here
            info('not json ' . json_encode($textResponse));
            dd('not json');
        }

        /** @var array<string,mixed> */
        return json_decode($string, true);
    }
}
