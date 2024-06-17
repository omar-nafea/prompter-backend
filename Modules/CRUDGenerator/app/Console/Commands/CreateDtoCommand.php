<?php

declare(strict_types=1);

namespace Modules\CRUDGenerator\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

final class CreateDtoCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name} {from-request?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        parent::handle();
        cache()->put('last_created_dto', $this->argument('name'));

        return self::SUCCESS;
    }

    /**
     * Execute the console command.
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
        if ($requestClass = $this->argument('from-request')) {
            $stub = str_replace(['{{ requestClass }}', '{{requestClass}}'], $requestClass, $stub);
        }

        return $stub;
    }

    protected function getStub()
    {
        return $this->resolveStubPath(
            $this->argument('from-request') ? '/stubs/dto-from-request.stub' : '/stubs/dto.stub'
        );
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
