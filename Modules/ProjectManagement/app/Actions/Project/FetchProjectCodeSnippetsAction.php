<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Modules\ProjectManagement\app\CodeSnippets\Builder\CodeSnippetBuilder;
use Modules\ProjectManagement\app\Enums\ProgrammingLanguage;
use Modules\ProjectManagement\app\Models\Project;

class FetchProjectCodeSnippetsAction
{
    public function __construct(
        protected CodeSnippetBuilder $codeSnippetBuilder
    ) {}

    public function execute(int $projectId)
    {
        $allowedLanguages = ProgrammingLanguage::cases();
        $project = Project::query()
//               ->allowedForUser()//todo use this scope
            ->findOrFail($projectId);
        $codeSnippets = [];
        foreach ($allowedLanguages as $language) {
            $codeSnippets[] = $this->codeSnippetBuilder->forProject($project)
                ->language($language)
                ->build();
        }

        return $codeSnippets;
    }
}
