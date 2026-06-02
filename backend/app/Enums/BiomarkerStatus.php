<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Semantic status for a biomarker reading.
 *
 * These are educational ranges, NOT clinical thresholds. They drive the
 * card colors/icons in the mobile dashboard.
 */
enum BiomarkerStatus: string
{
    case NORMAL = 'normal';
    case ATTENTION = 'attention';
    case ALERT = 'alert';

    /** Hex color consumed by the mobile theme (mirrored in mobile/src/theme). */
    public function color(): string
    {
        return match ($this) {
            self::NORMAL => '#16A34A',    // green
            self::ATTENTION => '#D97706',  // amber
            self::ALERT => '#DC2626',      // red
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Within range',
            self::ATTENTION => 'Needs attention',
            self::ALERT => 'Out of range',
        };
    }
}
