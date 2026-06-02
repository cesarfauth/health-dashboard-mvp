<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use RuntimeException;

/**
 * Thrown by a provider when no API key is configured, so the resilient client
 * can fall back without attempting a doomed network call.
 */
class MissingApiKeyException extends RuntimeException {}
