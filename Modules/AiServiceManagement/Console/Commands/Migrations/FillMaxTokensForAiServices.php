<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Modules\AiServiceManagement\app\Models\AiService;

final class FillMaxTokensForAiServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-max-tokens-for-ai-services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fill max tokens for ai services');
        $data = [
            'GPT 3.5' => 16384,
            'GPT 4.0' => 128000,
            'GPT 4o' => 128000,
            'Gemini' => 0,
        ];
        foreach ($data as $name => $value) {
            AiService::where('name', $name)->firstOrFail()->update(['max_tokens' => $value]);
        }

        return self::SUCCESS;
    }
}
