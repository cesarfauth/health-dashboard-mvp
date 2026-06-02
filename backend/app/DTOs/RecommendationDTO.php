<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * A single actionable habit recommendation produced by the LLM.
 */
final readonly class RecommendationDTO
{
    public function __construct(
        public string $title,
        public string $detail,
        public string $category,
    ) {}

    /**
     * Build from a loosely-typed array (LLM JSON), defensively coercing fields.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: trim((string) ($data['title'] ?? '')),
            detail: trim((string) ($data['detail'] ?? '')),
            category: trim((string) ($data['category'] ?? 'general')) ?: 'general',
        );
    }

    /** @return array{title: string, detail: string, category: string} */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'detail' => $this->detail,
            'category' => $this->category,
        ];
    }
}
