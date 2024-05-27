<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Modules\ProjectManagement\app\Actions\Project\GenerateProjectApiKeyAction;
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
            fn () => Pipeline::send(['dto' => $dto])
                ->through([
                    $this->storeProjectData(...),
                    $this->storeProjectInputs(...),
                    $this->storeObjectiveQuestions(...),
                    $this->storeProjectOutputs(...),
                ])->then(fn ($params) => $params['project']),
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
        $project->inputs()->createMany($dto->projectInputs->toArray());

        return $next($params);
    }

    protected function storeProjectOutputs(array $params, Closure $next)
    {
        /* @var StoreProjectDto $dto */
        $dto = $params['dto'];

        /** @var Project $project */
        $project = $params['project'];
        $project->outputs()->createMany($dto->projectOutputs->toArray());

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
