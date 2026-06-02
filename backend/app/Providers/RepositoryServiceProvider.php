<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\HealthRecordRepositoryInterface;
use App\Repositories\Eloquent\HealthRecordRepository;
use App\Services\Integrations\Claude\ClaudeClientInterface;
use App\Services\Integrations\Claude\ClaudeService;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository (and integration) interfaces to their concrete
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

        // The live SDK implementation; it self-degrades to FallbackClaudeService
        // when no API key is set or a call fails.
        ClaudeClientInterface::class => ClaudeService::class,
    ];
}
