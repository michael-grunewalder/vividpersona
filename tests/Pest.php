<?php

use App\Enums\AiModelType;
use App\Enums\ApiService;
use App\Models\AiModel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Create an enabled image model, defaulting to a FAL nano-banana endpoint.
 */
function imageModel(array $attributes = []): AiModel
{
    return AiModel::factory()->create(array_merge([
        'type' => AiModelType::Image,
        'provider' => ApiService::Fal,
        'endpoint' => 'fal-ai/nano-banana-2',
        'options' => [
            'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
            'defaults' => ['num_images' => 1],
        ],
    ], $attributes));
}
