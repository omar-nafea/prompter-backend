<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Modules\ProjectManagement\app\Actions\Project\GenerateProjectApiKeyAction;
use Modules\ProjectManagement\app\Dtos\Project\ProjectInputDto;
use Modules\ProjectManagement\app\Dtos\Project\StoreProjectDto;
use Modules\ProjectManagement\app\Models\Project;

final class StoreProjectAction
{
    public function __construct(
        protected GenerateProjectApiKeyAction $generateProjectApiKeyAction
    ) {}

    public function execute(StoreProjectDto $dto)
    {

        return DB::transaction(
            fn() => Pipeline::send(['dto' => $dto])
                ->through([
                    $this->storeProjectData(...),
                    $this->storeProjectInputs(...),
                    $this->storeObjectiveQuestions(...),
                    $this->storeProjectOutputs(...),
                ])->then(fn($params) => $params['project']),
        );
    }

    protected function storeProjectData(array $params, Closure $next)
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];
        $params['project'] = $dto->creator->projects()->create($dto->projectDto->toArray() + [
            'api_key' => $this->generateProjectApiKeyAction->execute(),
        ]);
        $params['project']->outputLanguages()->attach($dto->outputLanguages);

        return $next($params);
    }

    protected function storeObjectiveQuestions(array $params, Closure $next)
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        /** @var Project $project */
        $project = $params['project'];

        //        /** @var ObjectiveQuestionDto $objectiveQuestion */
        //        foreach ( $dto->objectiveQuestions as $objectiveQuestion){
        $project->answers()->createMany($dto->objectiveQuestions->toArray());

        //            ProjectObjectiveAnswer::create([
        //                'project_id' => $project->id,
        //                'question_id' => $objectiveQuestion->questionId,
        //                'answer' => $objectiveQuestion->answer
        //            ]);
        //        }
        return $next($params);
    }

    protected function storeProjectInputs(array $params, Closure $next)
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
                        collect($projectInput->values)->map(fn($enumValue) => ['value' => $enumValue])
                    );
            }
        }

        return $next($params);
    }

    protected function storeProjectOutputs(array $params, Closure $next)
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        /** @var Project $project */
        $project = $params['project'];
        /** @var ProjectInputDto $projectInput */
        foreach ($dto->projectOutputs as $projectOutput) {
            $output = $project->outputs()->create($projectOutput->except('values')->toArray());
            if ($projectOutput->values) {
                $output->enumValues()
                    ->createMany(
                        collect($projectOutput->values)->map(fn($enumValue) => ['value' => $enumValue])
                    );
            }
        }

        return $next($params);
    }

    protected function sample(array $params, Closure $next)
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        return $next($params);
    }

    protected function generateApiKey(array $params, Closure $next)
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        return $next($params);
    }
}
