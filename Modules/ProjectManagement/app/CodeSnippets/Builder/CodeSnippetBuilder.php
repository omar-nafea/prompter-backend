<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\CodeSnippets\Builder;

use Modules\ProjectManagement\app\Enums\ProgrammingLanguage;
use Modules\ProjectManagement\app\Models\Project;

class CodeSnippetBuilder
{
    private ProgrammingLanguage $language;

    private Project $project;

    public function language(ProgrammingLanguage $language)
    {
        $this->language = $language;

        return $this;
    }

    public function forProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    public function build(): array
    {
        $codeSnippetDirPath = module_path('ProjectManagement', "app/CodeSnippets/{$this->language->value}");
        if ( ! is_dir($codeSnippetDirPath)) {
            $snippets = [];
            $snippets[$this->language->value][] = [
                'snippet' => 'No snippets found',
                'type' => $this->language->value,
            ];

            return $snippets;
        }
        $snippetTypes = collect(scandir($codeSnippetDirPath))->filter(fn($file) => ! in_array($file, ['.', '..']));
        $snippets = [];
        foreach ($snippetTypes as $snippetType) {
            $code = file_get_contents($codeSnippetDirPath . '/' . $snippetType);
            $code = str_replace(
                ['{{END_POINT}}', '{{API_KEY}}', '{{PUBLIC_KEY}}', '{{INPUTS}}'],
                [
                    config('app.url') . config('project-management.code_snippet_endpoint'),
                    $this->project->api_key,
                    $this->project->key,
                    $this->buildInputs($snippetType),
                ],
                $code
            );
            //           $inputs = $this->buildInputs();
            $snippets[$this->language->value][] = [
                'snippet' => $code,
                'type' => $snippetType,
            ];
            //           $snippets[$this->language->value][$snippetType] = $code;
        }

        return $snippets;
    }

    protected function buildInputs(string $snippetType): string
    {
        //todo add inputs syntax samples
        //todo add inputs values samples with comments
        //        $inputs = $this->project->inputs->mapWithKeys(fn($input) => [
        //            $input->name => $input->data_type->example(),
        //        ])->toArray();
        $inputs = match ($this->inputSyntax($snippetType)) {
            'array' => $this->arrayInputsSample(),
            'json' => $this->jsonInputsSample(),
        };

        return str_replace(['array (', ')'], ['[', ']'], var_export($inputs, true));
    }

    protected function inputSyntax(string $snippetType): string
    {
        return match ($snippetType) {
            'cURL', 'HttpClient (Laravel)' => 'array',
            default => 'json',
        };
    }

    protected function arrayInputsSample(): array
    {
        return $this->project->inputs->mapWithKeys(fn($input) => [
            $input->name => $input->data_type->example(),
        ])->toArray();
    }

    protected function jsonInputsSample(): string
    {
        return json_encode($this->arrayInputsSample(), JSON_PRETTY_PRINT);
    }
}
