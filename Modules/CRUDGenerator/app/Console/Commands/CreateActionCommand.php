<?php

declare(strict_types=1);

namespace Modules\CRUDGenerator\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class CreateActionCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name} {dto}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
        if ($requestClass = $this->argument('dto') ?? cache()->get('last_created_dto')) {
            $stub = str_replace(['{{ dtoClass }}', '{{dtoClass}}'], $requestClass, $stub);
        }

        return $stub;
    }

    protected function getStub()
    {
        return $this->resolveStubPath(
            '/stubs/action.stub'
        );
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
