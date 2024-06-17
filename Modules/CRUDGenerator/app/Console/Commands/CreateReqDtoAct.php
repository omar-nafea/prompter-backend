<?php

declare(strict_types=1);

namespace Modules\CRUDGenerator\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\text;

final class CreateReqDtoAct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:create-req-dto-act';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $operation = text('Operation');
        $requestName = $operation . 'Request';
        $dtoName = $operation . 'Dto';
        $actionName = $operation . 'Action';

        Artisan::call('make:api-request', ['name' => $requestName]);
        $this->info(Artisan::output());

        Artisan::call('make:dto', ['name' => $dtoName, 'from-request' => $requestName]);
        $this->info(Artisan::output());

        Artisan::call('make:action', ['name' => $actionName, 'dto' => $dtoName]);
        $this->info(Artisan::output());

        return self::SUCCESS;
    }
}
