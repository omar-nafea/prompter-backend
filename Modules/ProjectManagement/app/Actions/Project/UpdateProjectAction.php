<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Modules\ProjectManagement\app\Dtos\Project\ObjectiveQuestionDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectInputDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectOutputDto;
use Modules\ProjectManagement\app\Dtos\Project\UpdateProjectDto;
use Modules\ProjectManagement\app\Models\Project;

final class UpdateProjectAction
{
    public function execute(UpdateProjectDto $dto): Project
    {
        /** @var Project */
        return DB::transaction(
            fn () => Pipeline::send(['dto' => $dto])
                ->through([
                    $this->updateProjectData(...),
                    $this->updateProjectInputs(...),
                    $this->updateObjectiveQuestions(...),
                    $this->updateProjectOutputs(...),
                ])->then(/** @param array{dto: UpdateProjectDto} $params */
                    destination: static fn (array $params): Project => $dto->project
                ),
        );
    }

    /**
     * @param  array{dto: UpdateProjectDto}  $params
     */
    protected function updateProjectData(array $params, Closure $next): Project
    {
        /* @var UpdateProjectDto $dto */
        $dto = $params['dto'];
        $dto->project->update($dto->projectDto->toArray());
        $dto->project->outputLanguages()->sync($dto->outputLanguages);
        $dto->project->details()->updateOrCreate([], [
            'ai_temperature' => $dto->projectDetailsDto->aiTemperature ?? 0.9,
        ]);

        return $next($params);
    }

    /**
     * @param  array{dto: UpdateProjectDto}  $params
     */
    protected function updateProjectInputs(array $params, Closure $next): Project
    {
        $dto = $params['dto'];
        /** @var ProjectInputDto $projectInput */
        foreach ($dto->projectInputs as $projectInput) {
            $inputData = $projectInput->except('values', 'id')->toArray();
            if ($projectInput->id) {
                $input = $dto->project->inputs()
                    ->findOrFail($projectInput->id);
                $input->update($inputData);
            } else {
                $input = $dto->project->inputs()->create($inputData);
            }
            if ($projectInput->values) {
                $input->enumValues()->delete();
                $input->enumValues()
                    ->createMany(
                        collect($projectInput->values)->map(static fn ($enumValue) => ['value' => $enumValue])
                    );
            }
        }

        return $next($params);
    }

    /**
     * @param  array{dto: UpdateProjectDto}  $params
     */
    protected function updateObjectiveQuestions(array $params, Closure $next): Project
    {
        $dto = $params['dto'];
        /** @var ObjectiveQuestionDto $objectiveQuestion */
        foreach ($dto->objectiveQuestions as $objectiveQuestion) {
            $dto->project->answers()
                ->findOrFail($objectiveQuestion->answerId)
                ->update($objectiveQuestion->except('answerId')->toArray());
        }

        return $next($params);
    }

    /**
     * @param  array{dto: UpdateProjectDto}  $params
     */
    protected function updateProjectOutputs(array $params, Closure $next): Project
    {
        $dto = $params['dto'];
        /** @var ProjectOutputDto $projectOutput */
        foreach ($dto->projectOutputs as $projectOutput) {
            $outputData = $projectOutput->except('values', 'id')->toArray();
            if ($projectOutput->id) {
                $output = $dto->project->outputs()
                    ->findOrFail($projectOutput->id);
                $output->update($outputData);
            } else {
                $output = $dto->project->outputs()->create(
                    $outputData
                );
            }
            if ($projectOutput->values) {
                $output->enumValues()->delete();
                $output->enumValues()
                    ->createMany(
                        collect($projectOutput->values)->map(static fn ($enumValue) => ['value' => $enumValue])
                    );
            }
        }

        return $next($params);
    }
}
