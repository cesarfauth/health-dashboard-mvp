<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\BiomarkerStatus;

/**
 * Classifies a biomarker reading into a semantic status using the ranges
 * declared in config/biomarkers.php. Pure, side-effect-free logic so it is
 * trivial to unit test and to reuse from Resources.
 */
final class BiomarkerClassifier
{
    /**
     * @param  string  $metric  One of the keys in config/biomarkers.php
     */
    public static function classify(string $metric, int|float $value): BiomarkerStatus
    {
        $range = config("biomarkers.$metric");

        if ($range === null) {
            return BiomarkerStatus::NORMAL;
        }

        if ($value >= $range['normal_min'] && $value <= $range['normal_max']) {
            return BiomarkerStatus::NORMAL;
        }

        if ($value >= $range['attention_min'] && $value <= $range['attention_max']) {
            return BiomarkerStatus::ATTENTION;
        }

        return BiomarkerStatus::ALERT;
    }
}
