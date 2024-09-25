<?php

declare(strict_types=1);

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT4_0Turbo\ChatGPT4_0Turbo;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\AskRequest;
use Modules\ProjectManagement\app\Actions\Project\CheckProjectPromptHasExceededMaxTokensAction;
use Modules\ProjectManagement\app\Models\Project;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

Route::middleware('api')->group(
    function (): void {
        Route::get('api/test', function () {
            //            $data = \Modules\AiServiceManagement\app\Models\AiCallRequestLog::latest()->simplePaginate(1);
            $data = Modules\AiServiceManagement\app\Models\AiCallRequestLog::select(['id', 'response'])->get()->toArray();
            $data = collect($data)
                ->map(function ($item) {
                    return [
                        'type' => $item['response']['data']['messageType'] ?? null,
                        'id' => $item['id'],
                    ];
                })
                ->filter(function ($item) {
                    return Str::contains($item['type'], 'Coupon', true);

                    return $item['request_body']['Name'] === 'shady rasmy';
                });
            $data = Modules\AiServiceManagement\app\Models\AiCallRequestLog::latest()->whereIn('id', $data->pluck('id'))->simplePaginate(1);

            return view('test', compact('data'));
            //            \Modules\AiServiceManagement\app\Models\AiCallRequestLog::latest()->limit(5)->pluck('response')->toArray();
            //            $client = new \GuzzleHttp\Client();
            //
            //            $response = $client->request('POST', 'domain.com/chat', [
            //                'multipart' => [
            //                    [
            //                        'name' => 'prompt',
            //                        'contents' => 'Hi, who is the most popular movie series actress now?'
            //                    ],
            //                    [
            //                        'name' => 'model',
            //                        'contents' => 'GPT-4'
            //                    ],
            //                    [
            //                        'name' => 'content',
            //                        'contents' => 'You\'re my helpful'
            //                    ],
            //                    [
            //                        'name' => 'memo',
            //                        'contents' => 'false'
            //                    ]
            //                ],
            //	'headers' => [
            //                'x-rapidapi-host' => 'host',
            //                'x-rapidapi-key' => 'api_key',
            //            ],
            //]);
            //            echo $response->getBody();
            //dd();
            //echo $response->getBody();
            $prompt = 'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy in json format. The output should specify the messageType:The type of message (don’t deviate from those types) Coupon Offers on products Buy subscription New products Weather Season Availability of missing products based on news Pets products Baby products Membership , messageContent:The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user) , communicationChannels: The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list. , messageHeader:A concise title for the message. , sendTiming:Recommended sending time for each channel. (time of the day only in 24h format)  accordingly. The output should be json only and nothing else in the message . Consider the Name,First time user,Classification,Previous Classification,Top 10 Products Purchased,Top 3 Categories,History of Previous Messages,Weather Conditions,Occasions,Season,News about Groceries,Consumes Baby Products,Consumes Pet Products,Salary Timing,Area,Device Type,Chosen Application display Language to tailor the response accordingly.### Tawfeer Market Context:Tawfeer Market is an online grocery startup in Egypt that offers a convenient and affordable way to shop for groceries online and get them delivered to your door in less than 2 hours. Tawfeer has its own fulfillment centers and stores, which gives it more control over its supply chain and value delivery. Tawfeer also has a powerful in-house marketing department that reaches its target market with cost-efficiency and low customer acquisition cost.### Available coupons[First50] discount of 50EGP for a minimum order of 750 EGP valid for the first order only[FreeD] Discount of 20EGP for a minimum order of 500 EGP valid for all customersNever propose a coupon that is not listed here### Products currently at discountWhite tissue kitchen towel 3+1 Roll 77.95 RGP instead of 91.50 EGPCapucci 1 pc 7.00 EGP instead of 10.00 EGP. The output should be json only and nothing else in the message. -Name:Rawda Elshahawy -First time user:No Made several orders before -Classification:Bought recently, buy often and spend the most! We should Reward them. Can be early adopters for new products. Will promote your brand. -Previous Classification:No Made several orders before -Top 10 Products Purchased:عيش فينو أبيض || جبنة شيدر طبيعي || المراعي  حليب كامل الدسم || جبنة جودة فريكو || خيار || تفاح أحمر مستورد || جزر || كرتونة مياه بركه 600 مل || لبن جهينة ميكس شيكولاتة || موز بلدي -Top 3 Categories:عروض غذائية || الخضروات || البان -History of Previous Messages:NA -Weather Conditions:Sunny -Occasions:NA -Season:School, Spring -News about Groceries:NA -Consumes Baby Products:1 -Consumes Pet Products: -Salary Timing: -Area:سموحة -Device Type:Android -Chosen Application display Language:English . Application display Language: .Generate a json output that includes the following: [messageType]:The type of message (don’t deviate from those types) Coupon Offers on products Buy subscription New products Weather Season Availability of missing products based on news Pets products Baby products Membership . [messageContent]:The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user) . [communicationChannels]: The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list. . [messageHeader]:A concise title for the message. . [sendTiming]:Recommended sending time for each channel. (time of the day only in 24h format)  . The output should be (Maximum 1000)';

            $z = app(ChatGPT4_0Turbo::class)
                ->ask(
                    new AskPayloadDto(
                        $prompt,
                        Project::latest()->first()
                    )
                );
            dd($z);
            //            (new Modules\Auth\app\Actions\ForgetPasswordAction())->execute(
            //                new Modules\Auth\app\Dtos\ForgetPasswordDto(
            //                    App\ValueObjects\Email::from('test@test.com')
            //                )
            //            );
            //            dd('123');
            //            $reset  = DB::table('password_reset_tokens')->where('email', 'test@test.com')->first();
            (new Modules\Auth\app\Actions\ResetPasswordAction())->execute(
                new Modules\Auth\app\Dtos\ResetPasswordDto(
                    App\ValueObjects\Email::from('test@test.com'),
                    'f11b5c8f6efcd66da48fa474b61d53b4a2089566e06fecb985bff2f6b2fa123a',
                    '123456789',
                    '123456789'
                )
            );
            dd('123');
            $projects = Project::allowedForUser(
                Modules\Auth\app\Models\User::findOrFail(21)
            )->get();

            return Modules\ProjectManagement\app\Http\Resources\ProjectResource::collection($projects);

            return [
                'x' => Project::allowedForUser(
                    Modules\Auth\app\Models\User::findOrFail(21)
                )->get()->map(fn (Project $project) => $project->is_owner),
            ];
            //            return new \App\Mail\ProjectModeratorInvitationMail(\Modules\ProjectManagement\app\Models\Project::latest()->first());
            //            Mail::to('asd@asdasd.com')->send(new \App\Mail\ProjectModeratorInvitationMail(\Modules\ProjectManagement\app\Models\Project::latest()->first()));
            //            dd("123");
            //            dd(Modules\ProjectManagement\app\Enums\ProjectOutputFormat::enabledCases());

            dd('warning real ai call');
            //            $resposne = "
            //            {\"text\":\"```json{ \"messageType\": \"Coupon Offers on products\", \"messageContent\": \"Exclusive offer: Get 20
            //EGP off on your next purchase of 500 EGP or more at Tawfeer Market!\", \"communicationChannels\": [\"Email\", \"SMS\",
            //\"Push Notification\", \"WhatsApp\"], \"messageHeader\": \"Special Discount for You!\", \"sendTiming\": {
            //\"Email\": \"09:00\", \"SMS\": \"10:30\", \"Push Notification\": \"14:00\", \"WhatsApp\": \"16:30\"
            //}}```\",\"finish_reason\":\"stop\",\"model\":\"gpt-3.5-turbo-030\",\"server\":\"backup-K\"}";
            //            dd($resposne);

            $prompt = 'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy in JSON format. The output should specify the type of message, the message content, the recommended communication channels, the message header, and the optimal timing for each channel. Consider the customer\'s classification, purchase history, product and category preferences, weather conditions, significant occasions, season, news about groceries, pet and baby product consumption, salary timing, geographic area, and device type to tailor the message accordingly.  The output should be JSON only and nothing else in the message  ### Customer Data: - Name: Rawda Elshahawy - First time user: No Made several orders before - Classification: Bought recently, buy often and spend the most! We should Reward them. Can be early adopters for new products. Will promote your brand. - Top 10 Products Purchased: عيش فينو أبيض || جبنة شيدر طبيعي || المراعي  حليب كامل الدسم || جبنة جودة فريكو || خيار || تفاح أحمر مستورد || جزر || كرتونة مياه بركه 600 مل || لبن جهينة ميكس شيكولاتة || موز بلدي - Top 3 Categories: عروض غذائية || الخضروات || البان - History of Previous Messages: NA - Weather Conditions: Sunny - Occasions: NA - Season: School, Spring - News about Groceries: NA - Consumes Baby Products:No - Consumes Pet Products: No - Salary Timing: No - Area: سموحة - Device Type: Android - Chosen Application display Language: English  ### Tawfeer Market Context: Tawfeer Market is an online grocery startup in Egypt that offers a convenient and affordable way to shop for groceries online and get them delivered to your door in less than 2 hours. Tawfeer has its own fulfillment centers and stores, which gives it more control over its supply chain and value delivery. Tawfeer also has a powerful in-house marketing department that reaches its target market with cost-efficiency and low customer acquisition cost. ### Available coupons [First50] discount of 50EGP for a minimum order of 750 EGP valid for the first order only [FreeD] Discount of 20EGP for a minimum order of 500 EGP valid for all customers Never propose a coupon that is not listed here  ### Products currently at discount White tissue kitchen towel 3+1 Roll 77.95 RGP instead of 91.50 EGP Capucci 1 pc 7.00 EGP instead of 10.00 EGP  Generate a JSON output that includes the following for one message only: [messageType]: The type of message (don’t deviate from those types) Coupon Offers on products Buy subscription New products Weather Season Availability of missing products based on news Pets products Baby products Membership 2. [messageContent]: The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user) 3. [communicationChannels]: The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list. 4. [messageHeader]: A concise title for the message. 5. [sendTiming]: Recommended sending time for each channel. (time of the day only in 24h format)';
            $prompt = 'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy in json format. The output should specify the messageType:The type of message (don’t deviate from those types) Coupon Offers on products Buy subscription New products Weather Season Availability of missing products based on news Pets products Baby products Membership , messageContent:The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user) , communicationChannels: The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list. , messageHeader:A concise title for the message. , sendTiming:Recommended sending time for each channel. (time of the day only in 24h format)  accordingly. The output should be json only and nothing else in the message . Consider the Name,First time user,Classification,Previous Classification,Top 10 Products Purchased,Top 3 Categories,History of Previous Messages,Weather Conditions,Occasions,Season,News about Groceries,Consumes Baby Products,Consumes Pet Products,Salary Timing,Area,Device Type,Chosen Application display Language to tailor the response accordingly.### Tawfeer Market Context:Tawfeer Market is an online grocery startup in Egypt that offers a convenient and affordable way to shop for groceries online and get them delivered to your door in less than 2 hours. Tawfeer has its own fulfillment centers and stores, which gives it more control over its supply chain and value delivery. Tawfeer also has a powerful in-house marketing department that reaches its target market with cost-efficiency and low customer acquisition cost.### Available coupons[First50] discount of 50EGP for a minimum order of 750 EGP valid for the first order only[FreeD] Discount of 20EGP for a minimum order of 500 EGP valid for all customersNever propose a coupon that is not listed here### Products currently at discountWhite tissue kitchen towel 3+1 Roll 77.95 RGP instead of 91.50 EGPCapucci 1 pc 7.00 EGP instead of 10.00 EGP. The output should be json only and nothing else in the message. -Name:Rawda Elshahawy -First time user:No Made several orders before -Classification:Bought recently, buy often and spend the most! We should Reward them. Can be early adopters for new products. Will promote your brand. -Previous Classification:No Made several orders before -Top 10 Products Purchased:عيش فينو أبيض || جبنة شيدر طبيعي || المراعي  حليب كامل الدسم || جبنة جودة فريكو || خيار || تفاح أحمر مستورد || جزر || كرتونة مياه بركه 600 مل || لبن جهينة ميكس شيكولاتة || موز بلدي -Top 3 Categories:عروض غذائية || الخضروات || البان -History of Previous Messages:NA -Weather Conditions:Sunny -Occasions:NA -Season:School, Spring -News about Groceries:NA -Consumes Baby Products:1 -Consumes Pet Products: -Salary Timing: -Area:سموحة -Device Type:Android -Chosen Application display Language:English . Application display Language: .Generate a json output that includes the following: [messageType]:The type of message (don’t deviate from those types) Coupon Offers on products Buy subscription New products Weather Season Availability of missing products based on news Pets products Baby products Membership . [messageContent]:The specific message to be communicated. (Maximum 142 characters, make sure not to mention the customer classification, as much as possible try to customize the message to the user) . [communicationChannels]: The recommended channels for this message (Email, SMS, Push Notification, WhatsApp) don’t deviate from the provided list. . [messageHeader]:A concise title for the message. . [sendTiming]:Recommended sending time for each channel. (time of the day only in 24h format)  . The output should be (Maximum 1000)';
            $client = new GuzzleHttp\Client();
            dd(json_decode('[
    {
        "content": "Hello! I\'m an AI assistant bot based on ChatGPT 3. How may I help you?",
        "role": "system"
    },
    {
        "content": "' . $prompt . '",
        "role": "user"
    }
]', true));

            $response = $client->request('POST', 'https://chatgpt-api8.p.rapidapi.com/', [
                'body' => '[
    {
        "content": "Hello! I\'m an AI assistant bot based on ChatGPT 3. How may I help you?",
        "role": "system"
    },
    {
        "content": "' . $prompt . '",
        "role": "user"
    }
]',
                'headers' => [
                    'X-RapidAPI-Host' => 'chatgpt-api8.p.rapidapi.com',
                    'X-RapidAPI-Key' => 'e42598e631msh96f908f88a4e748p1729efjsna6cff3214fcb',
                    'content-type' => 'application/json',
                ],
            ]);

            return response(
                $response->getBody()
            );
        });
        Route::get('api/test2', function () {
            $mockClient = new MockClient([
                AskRequest::class => MockResponse::make(body: '{"text":"```json\n{\n \"messageType\": \"fake response 2xCoupon Offers on products\",\n \"messageContent\": \"Enjoy discounts on selected products this Spring at Tawfeer Market!\",\n \"communicationChannels\": [\"Email\", \"SMS\", \"Push Notification\", \"WhatsApp\"],\n \"messageHeader\": \"Spring Savings at Tawfeer Market!\",\n \"sendTiming\": {\n \"Email\": \"10:00\",\n \"SMS\": \"15:00\",\n \"Push Notification\": \"17:30\",\n \"WhatsApp\": \"12:00\"\n }\n}\n```","finish_reason":"stop","model":"gpt-3.5-turbo-030","server":"backup-K"}', status: 200),
            ]);
            $connector = new Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0Connector();
            $connector->withMockClient($mockClient);
            $x = $connector->send(new AskRequest());

            return response($x->dtoOrFail()->toArray());

            return response(
                json_decode(str($x->json('text'))->remove('```')->remove('json')->trim()->toString(), true)
            );
        });
        Route::get('api/test3', function (): void {
            //            dd(Project::where('key','lYpM5qBPu5r97wB95d79196')->firstOrFail());
            //            Artisan::call('cache:clear');
            $result = app(CheckProjectPromptHasExceededMaxTokensAction::class)->execute(
                Project::where('key', 'lYpM5qBPu5r97wB95d79196')->firstOrFail()
            );
            dd($result);
        });
    }
);

Route::middleware('web')->group(
    function (): void {
        Route::get('test', function (): void {
            dd(
                123
            );
        });
    }
);
