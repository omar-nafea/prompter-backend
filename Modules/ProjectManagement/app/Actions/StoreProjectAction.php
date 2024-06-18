<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Modules\ProjectManagement\app\Actions\Project\GenerateProjectApiKeyAction;
use Modules\ProjectManagement\app\Dtos\Project\ProjectInputDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectOutputDto;
use Modules\ProjectManagement\app\Dtos\Project\StoreProjectDto;
use Modules\ProjectManagement\app\Models\Project;

final class StoreProjectAction
{
    public function __construct(
        protected GenerateProjectApiKeyAction $generateProjectApiKeyAction
    ) {}

    public function execute(StoreProjectDto $dto): Project
    {
        /** @var Project */
        return DB::transaction(
            fn () => Pipeline::send(['dto' => $dto])
                ->through([
                    $this->storeProjectData(...),
                    $this->storeProjectInputs(...),
                    $this->storeObjectiveQuestions(...),
                    $this->storeProjectOutputs(...),
                ])->then(/** @param array{dto: StoreProjectDto, project: Project} $params */
                    destination: static fn (array $params): Project => $params['project']
                ),
        );
    }

    /**
     * @param  array{dto: StoreProjectDto, project: Project}  $params
     */
    protected function storeProjectData(array $params, Closure $next): Project
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];
        $params['project'] = $dto->creator->projects()->create(
            $dto->projectDto->toArray() + [
                'api_key' => $this->generateProjectApiKeyAction->execute(),
            ]
        );
        $params['project']->outputLanguages()->attach($dto->outputLanguages);

        return $next($params);
    }

    /**
     * @param  array{dto: StoreProjectDto, project: Project}  $params
     */
    protected function storeObjectiveQuestions(array $params, Closure $next): Project
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        /** @var Project $project */
        $project = $params['project'];
        $project->answers()->createMany($dto->objectiveQuestions->toArray());

        return $next($params);
    }

    /**
     * @param  array{dto: StoreProjectDto, project: Project}  $params
     */
    protected function storeProjectInputs(array $params, Closure $next): Project
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        /** @var Project $project */
        $project = $params['project'];
        /** @var ProjectInputDto $projectInput */
        foreach ($dto->projectInputs as $projectInput) {
            $input = $project->inputs()->create($projectInput->except('values')->toArray());
            if ($projectInput->values) {
                $input->enumValues()
                    ->createMany(
                        collect($projectInput->values)->map(fn ($enumValue) => ['value' => $enumValue])
                    );
            }
        }

        return $next($params);
    }

    /**
     * @param  array{dto: StoreProjectDto, project: Project}  $params
     */
    protected function storeProjectOutputs(array $params, Closure $next): Project
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        /** @var Project $project */
        $project = $params['project'];
        /** @var ProjectOutputDto $projectOutput */
        foreach ($dto->projectOutputs as $projectOutput) {
            $output = $project->outputs()->create($projectOutput->except('values')->toArray());
            if ($projectOutput->values) {
                $output->enumValues()
                    ->createMany(
                        collect($projectOutput->values)->map(fn ($enumValue) => ['value' => $enumValue])
                    );
            }
        }

        return $next($params);
    }

    /**
     * @param  array{dto: StoreProjectDto, project: Project}  $params
     */
    protected function sample(array $params, Closure $next): Project
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        return $next($params);
    }
}
