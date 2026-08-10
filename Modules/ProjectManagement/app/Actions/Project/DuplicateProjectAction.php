<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Arr;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\app\Dtos\Project\DuplicateProjectDto;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInputEnumValue;
use Modules\ProjectManagement\app\Models\ProjectObjectiveAnswer;
use Modules\ProjectManagement\app\Models\ProjectOutputEnumValue;

final class DuplicateProjectAction
{
    protected array $commonAttributes = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_by',
        'created_by',
        'updated_by',
    ];

    public function __construct(
        protected GenerateProjectApiKeyAction $generateProjectApiKeyAction
    ) {}

    public function execute(DuplicateProjectDto $dto): Project
    {
        $project = $dto->authUser->projects()->where('key', $dto->projectKey)->firstOrFail();
        $project->load('inputs.enumValues', 'outputs.enumValues', 'outputLanguages', 'answers', 'aiModel', 'details');

        return DB::transaction(fn () => $this->handle($dto, $project));
    }

    private function handle(DuplicateProjectDto $dto, Project $project): Project
    {
        $duplicatedProject = $this->duplicateProject($dto, $project);
        $duplicatedProject->outputLanguages()->attach(
            $project->outputLanguages->pluck('id')->toArray()
        );
        $this->duplicateAiModel($project, $duplicatedProject);
        $this->duplicateDetails($project, $duplicatedProject);
        $this->duplicateInputs($project, $duplicatedProject);
        $this->duplicateOutputs($project, $duplicatedProject);
        $this->duplicateAnswers($project, $duplicatedProject);

        return $duplicatedProject;
    }

    public function duplicateProject(DuplicateProjectDto $dto, Project $project): Project
    {
        return $dto->authUser->projects()->create(array_merge(
            Arr::except(
                $project->toArray(),
                [
                    ...$this->commonAttributes,
                    'key',
                    'api_key',
                    'inputs',
                    'outputs',
                    'output_languages',
                    'answers',
                    'ai_model',
                    'details',
                ]
            ),
            [
                'name' => $project->name . ' - Copy',
                'api_key' => $this->generateProjectApiKeyAction->execute(),
            ]
        ));
    }

    private function duplicateAiModel(Project $project, Project $duplicatedProject): void
    {
        if ($project->aiModel === null) {
            return;
        }

        $duplicatedProject->aiModel()->create([
            'name' => $project->aiModel->name,
            'alias' => $project->aiModel->alias,
            'provider' => $project->aiModel->provider,
            'api_key' => $project->aiModel->api_key,
            'connector_url' => $project->aiModel->connector_url,
        ]);
    }

    private function duplicateDetails(Project $project, Project $duplicatedProject): void
    {
        if ($project->details === null) {
            return;
        }

        $duplicatedProject->details()->create(
            Arr::except($project->details->getAttributes(), [
                'id',
                'project_id',
                'created_at',
                'updated_at',
                'deleted_at',
                'created_by',
                'updated_by',
                'deleted_by',
            ])
        );
    }

    public function duplicateInputs(Project $project, Project $duplicatedProject): void
    {
        foreach ($project->inputs as $input) {
            $duplicatedInput = $duplicatedProject->inputs()->create(
                Arr::except($input->toArray(), [
                    ...$this->commonAttributes,
                    'project_id',
                    'enum_values',
                ])
            );
            if ($input->data_type === DataType::Enum) {
                $duplicatedInput->enumValues()->createMany(
                    $input->enumValues->map(
                        fn (ProjectInputEnumValue $inputEnumValue) => Arr::except($inputEnumValue->toArray(), [
                            ...$this->commonAttributes,
                            'project_input_id',
                        ])
                    )
                );
            }
        }
    }

    public function duplicateOutputs(Project $project, Project $duplicatedProject): void
    {
        foreach ($project->outputs as $output) {
            $duplicatedOutput = $duplicatedProject->outputs()->create(
                Arr::except($output->toArray(), [
                    ...$this->commonAttributes,
                    'project_id',
                    'enum_values',
                ])
            );
            if ($output->data_type === DataType::Enum) {
                $duplicatedOutput->enumValues()->createMany(
                    $output->enumValues->map(
                        fn (ProjectOutputEnumValue $outputEnumValue) => Arr::except($outputEnumValue->toArray(), [
                            ...$this->commonAttributes,
                            'project_output_id',
                        ])
                    )
                );
            }
        }
    }

    public function duplicateAnswers(Project $project, Project $duplicatedProject): void
    {
        $duplicatedProject->answers()->createMany(
            $project->answers->map(
                fn (ProjectObjectiveAnswer $answer) => Arr::except($answer->toArray(), [
                    ...$this->commonAttributes,
                    'project_id',
                ])
            )
        );
    }
}
