<?php

/*
|------------------------------------------------------------------------------
| Biomarker reference ranges (educational, NOT clinical)
|------------------------------------------------------------------------------
| Single source of truth for biomarker bounds, units and classification.
| Used by BiomarkerClassifier to map a reading to a BiomarkerStatus and by the
| API Resources to expose units/labels. Mirrored on the mobile side for the
| card colors. Tweaking a range here updates the whole system.
|
| Classification model per metric:
|   - "normal"    : value is inside [normal_min, normal_max]
|   - "attention" : value is inside [attention_min, attention_max] (the wider
|                    band that still surrounds normal)
|   - "alert"     : anything outside the attention band
*/

return [

    'sleep_hours' => [
        'label' => 'Sleep',
        'unit' => 'h',
        'normal_min' => 7.0,
        'normal_max' => 9.0,
        'attention_min' => 6.0,
        'attention_max' => 10.0,
    ],

    'glucose_level' => [
        'label' => 'Glucose (fasting)',
        'unit' => 'mg/dL',
        'normal_min' => 70,
        'normal_max' => 99,
        'attention_min' => 70,
        'attention_max' => 125,
    ],

    'hrv' => [
        'label' => 'HRV',
        'unit' => 'ms',
        // Higher HRV is generally better; low HRV is the concern.
        'normal_min' => 50,
        'normal_max' => 200,
        'attention_min' => 30,
        'attention_max' => 200,
    ],

];
