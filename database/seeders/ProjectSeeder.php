<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AiServiceManagement\app\Enums\AiCallType;
use Modules\ProjectManagement\app\Actions\Project\GenerateProjectApiKeyAction;
use Modules\ProjectManagement\app\Dtos\Project\ProjectDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectInputDto;
use Modules\ProjectManagement\app\Dtos\Project\ProjectOutputDto;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project = Project::create(
            (new ProjectDto(
                'tawfeer',
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
}
