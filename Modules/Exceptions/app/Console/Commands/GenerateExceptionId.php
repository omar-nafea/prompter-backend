<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Console\Commands;

use Illuminate\Console\Command;
use Str;

final class GenerateExceptionId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exception:generate-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate exception id';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info(
            Str::uuid()->toString()
        );

        return Command::SUCCESS;
    }
}
