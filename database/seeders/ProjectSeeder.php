<?php

declare(strict_types=1);

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Modules\AiServiceManagement\app\Enums\AiCallType;
use Modules\ProjectManagement\app\Actions\Project\GenerateProjectApiKeyAction;
use Modules\ProjectManagement\app\Dtos\Project\ProjectDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectInputDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectOutputDto;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\Project;

final class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //      $this->dummyProject();
        DB::transaction(fn () => $this->tawfeerProject());
    }

    protected function dummyProject(): void
    {
        $project = Project::create(
            (new ProjectDto(
                'tawfeer dummy',
                'generate a personalized marketing message strategy',
                1,
                AiCallType::OneByOne->value,
                1,
                1000,
                ProjectOutputFormat::Json,
            ))->toArray() + [
                'user_id' => 1,
                'api_key' => (new GenerateProjectApiKeyAction())->execute(),
            ]
        );
        $inputsData = [
            new ProjectInputDto(
                'Name',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'First time user',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Classification',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Previous Classification',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Top 10 Products Purchased',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Top 3 Categories',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'History of Previous Messages',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Weather Conditions',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Occasions',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Season',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'News about Groceries',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Consumes Baby Products',
                DataType::Boolean,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Consumes Pet Products',
                DataType::Boolean,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Salary Timing',
                DataType::Boolean,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Area',
                DataType::Boolean,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Device Type',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Chosen Application display Language',
                DataType::String,
                true,
                1000,
            ),
        ];
        $project->inputs()->createMany(collect($inputsData)->map->toArray());
        $outputsData = [
            new ProjectOutputDto(
                'messageType',
                DataType::String,
                true,
                500,
                'The type of message (don’t deviate from those types)
Coupon
Offers on products
Buy subscription
New products
Weather
Season
Availability of missing products based on news
Pets products
Baby products
Membership'
            ),
            new ProjectOutputDto(
                'messageContent',
                DataType::String,
                true,
                500,
                'The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user)'
            ),
            new ProjectOutputDto(
                'communicationChannels',
                DataType::String,
                true,
                500,
                ' The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list.'
            ),
            new ProjectOutputDto(
                'messageHeader',
                DataType::String,
                true,
                500,
                'A concise title for the message.'
            ),
            new ProjectOutputDto(
                'sendTiming',
                DataType::String,
                true,
                500,
                'Recommended sending time for each channel. (time of the day only in 24h format)'
            ),

        ];
        $project->outputs()->createMany(collect($outputsData)->map->toArray());
    }

    protected function tawfeerProject(): void
    {
        $project = Project::create(
            (new ProjectDto(
                'tawfeer',
                'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy',
                1,
                AiCallType::OneByOne->value,
                1,
                1000,
                ProjectOutputFormat::Json,
            ))->toArray() + [
                'user_id' => 1,
                'api_key' => (new GenerateProjectApiKeyAction())->execute(),
            ]
        );
        $project->outputLanguages()->attach([1]);
        $project->answers()->create([
            'project_objective_question_id' => 1,
            'answer' => '### Tawfeer Market Context:
Tawfeer Market is an online grocery startup in Egypt that offers a convenient and affordable way to shop for groceries online and get them delivered to your door in less than 2 hours. Tawfeer has its own fulfillment centers and stores, which gives it more control over its supply chain and value delivery. Tawfeer also has a powerful in-house marketing department that reaches its target market with cost-efficiency and low customer acquisition cost.

### Available coupons
[First50] discount of 50EGP for a minimum order of 750 EGP valid for the first order only
[FreeD] Discount of 20EGP for a minimum order of 500 EGP valid for all customers
Never propose a coupon that is not listed here

### Products currently at discount
White tissue kitchen towel 3+1 Roll 77.95 RGP instead of 91.50 EGP
Capucci 1 pc 7.00 EGP instead of 10.00 EGP',
        ]);
        $inputsData = [
            new ProjectInputDto(
                'Name',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'First time user',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Classification',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Previous Classification',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Top 10 Products Purchased',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Top 3 Categories',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'History of Previous Messages',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Weather Conditions',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Occasions',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Season',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'News about Groceries',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Consumes Baby Products',
                DataType::Boolean,
                true,
            ),
            new ProjectInputDto(
                'Consumes Pet Products',
                DataType::Boolean,
                true,
            ),
            new ProjectInputDto(
                'Salary Timing',
                DataType::Boolean,
                true,
            ),
            new ProjectInputDto(
                'Area',
                DataType::String,
                true,
            ),
            new ProjectInputDto(
                'Device Type',
                DataType::String,
                true,
                1000,
            ),
            new ProjectInputDto(
                'Chosen Application display Language',
                DataType::String,
                true,
                1000,
            ),
        ];
        foreach ($inputsData as $projectInput) {
            $input = $project->inputs()->create($projectInput->except('values')->toArray());
            if ($projectInput->values) {
                $input->enumValues()
                    ->createMany(
                        collect($projectInput->values)->map(fn ($enumValue) => ['value' => $enumValue])
                    );
            }
        }
        $outputsData = [
            new ProjectOutputDto(
                'messageType',
                DataType::String,
                true,
                1000,
                'The type of message (don’t deviate from those types) Coupon Offers on products Buy subscription New products Weather Season Availability of missing products based on news Pets products Baby products Membership'
            ),
            new ProjectOutputDto(
                'messageContent',
                DataType::String,
                true,
                1000,
                'The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user)'
            ),
            new ProjectOutputDto(
                'communicationChannels',
                DataType::String,
                true,
                500,
                'The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list.'
            ),
            new ProjectOutputDto(
                'messageHeader',
                DataType::String,
                true,
                500,
                'A concise title for the message.'
            ),
            new ProjectOutputDto(
                'sendTiming',
                DataType::String,
                true,
                500,
                'Recommended sending time for each channel. (time of the day only in 24h format)'
            ),

        ];
        foreach ($outputsData as $projectOutput) {
            $output = $project->outputs()->create($projectOutput->except('values')->toArray());
            if ($projectOutput->values) {
                $output->enumValues()
                    ->createMany(
                        collect($projectOutput->values)->map(fn ($enumValue) => ['value' => $enumValue])
                    );
            }
        }
    }
}
