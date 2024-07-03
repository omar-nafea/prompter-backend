<?php

declare(strict_types=1);

namespace MohamedGaber\PipelineFilter;

use Illuminate\Database\Eloquent\Builder;
use MohamedGaber\PipelineFilter\Contracts\PipeFilterDto;

abstract class BasePipeFilter
{
    public const NUMBER_REGEX = '/^[0-9]+$/';

    protected PipeFilterDto $dto;

    protected Builder $query;

    public function handle(PipeFilterDto $dto, $next)
    {
        $this->dto = $dto;
        $this->query = $dto->query;
        if ($this->shouldApplyFilter()) {
            $this->apply();
        }

        return $next($dto);
    }

    public function shouldApplyFilter()
    {
        return $this->value();
    }

    abstract public function value(): mixed;

    abstract public function apply(): void;
}
