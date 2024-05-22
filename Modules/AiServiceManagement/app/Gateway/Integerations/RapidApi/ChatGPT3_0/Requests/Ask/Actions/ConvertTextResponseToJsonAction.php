<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Actions;

final class ConvertTextResponseToJsonAction
{
    public function execute(?string $textResponse): array
    {
        return $this->prepareTextResponse($textResponse);
    }

    protected function prepareTextResponse(?string $textResponse): array
    {
        $string = str($textResponse)->remove('```')->remove('json')->trim()->toString();
        if (! json_validate($string)) {
            //todo add exception here
            dd('not json');
        }

        return json_decode($string, true);
    }
}
