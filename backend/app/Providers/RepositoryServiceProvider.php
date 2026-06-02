<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\HealthRecordRepositoryInterface;
use App\Repositories\Eloquent\HealthRecordRepository;
use App\Services\Integrations\Llm\AnthropicService;
use App\Services\Integrations\Llm\FallbackLlmService;
use App\Services\Integrations\Llm\LlmClientInterface;
use App\Services\Integrations\Llm\OpenAiService;
use App\Services\Integrations\Llm\ResilientLlmClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository and integration interfaces to their concrete
 * implementations. This is the single place where the application decides
 * "which class answers this contract" — the essence of Dependency Inversion.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Contract => implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        HealthRecordRepositoryInterface::class => HealthRecordRepository::class,
    ];

    public function register(): void
    {
        // Resolve the LLM provider chosen via LLM_PROVIDER, then wrap it in the
        // resilient decorator so a missing key / failed call degrades to the
        // deterministic fallback. Swapping providers is a one-line .env change.
        $this->app->bind(LlmClientInterface::class, function (Application $app) {
            $provider = (string) config('services.llm.provider', 'openai');

            $primary = match ($provider) {
                'anthropic' => $app->make(AnthropicService::class),
                default => $app->make(OpenAiService::class),
            };

            return new ResilientLlmClient($primary, $app->make(FallbackLlmService::class));
        });
    }
}
