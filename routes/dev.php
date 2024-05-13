<?php

declare(strict_types=1);

use Illuminate\Support\Str;

Route::middleware('api')->group(
    function () {
        Route::get('api/test', function () {});
    }
);

Route::middleware('web')->group(
    function () {
        Route::get('test', function () {
            dd(
                (new \Modules\ProjectManagement\app\Actions\Project\FetchSingleProjectAction())->execute('Hvpg8gCVUpKXlli14488fe9')
            );
            $x = \Modules\ProjectManagement\app\Models\Project::findByUniqueKey('Hvpg8gCVUpKXlli14488fe9');
            dd($x);

            dd(
                app(\MohamedGaber\UniqueModelKeyGenerator\Contracts\UniqueModelKeyGeneratorFactory::class)->generate()
            );
            $x = sprintf(
                '%s%s',
                $tokenEntropy = Str::random(15),
                hash('crc32b', $tokenEntropy)
            );
            //            $x = hash('sha256', $x);
            dd($x);
            $bytes = random_bytes(10);
            $x = bin2hex($bytes);
            dd($x);
            $x = (new \App\ModelKey())->encode();
            $salt = '12312312312321';
            //            dd(base64_encode($salt));
            $z = (new \App\ModelKey())->encryptWithSalt($x, $salt);
            $c = (new \App\ModelKey())->decryptWithSalt($z, $salt);
            dd($x, $z, $c);
            $x = (new \PascalDeVink\ShortUuid\ShortUuid())->encode(Str::orderedUuid());
            dd($x);
            dd(Str::uuid()->toString(), Str::orderedUuid()->toString());
            $model = \Modules\Auth\app\Models\User::first();
            dd(encrypt(1));
            dd(substr(strtolower(class_basename($model)), 0, 3) . '_');
        });
    }
);
