<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Exception;
use Modules\ProjectManagement\app\CodeSnippets\Builder\CodeSnippetBuilder;
use Modules\ProjectManagement\app\Enums\ProgrammingLanguage;
use Modules\ProjectManagement\app\Models\Project;

final class FetchProjectCodeSnippetsAction
{
    public function __construct(
        protected CodeSnippetBuilder $codeSnippetBuilder
    ) {}

    /**
     * @return array<int,array<string,mixed>>
     *
     * @throws Exception
     */
    public function execute(string $projectKey): array
    {
        $allowedLanguages = ProgrammingLanguage::cases();
        $project = Project::query()
//               ->allowedForUser()//todo use this scope
            ->where('key', $projectKey)->firstOrFail();
        $codeSnippets = [];
        foreach ($allowedLanguages as $language) {
            $codeSnippets[] = $this->codeSnippetBuilder->forProject($project)
                ->language($language)
                ->build();
        }

        return $codeSnippets;
    }
}
