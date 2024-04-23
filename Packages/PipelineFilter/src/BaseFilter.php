<?php

declare(strict_types=1);

namespace MohamedGaber\PipelineFilter;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pipeline\Pipeline;
use MohamedGaber\PipelineFilter\Contracts\PipeFilterDto;

abstract class BaseFilter
{
    public function apply(
        PipeFilterDto $dto
    ): EloquentBuilder {
        return (new Pipeline(app()))
            ->via('handle')
            ->send($dto)
            ->through($this->getPipes())
            ->then(fn (PipeFilterDto $dto) => $dto->query);
    }

    abstract protected function getPipes(): array;
}
