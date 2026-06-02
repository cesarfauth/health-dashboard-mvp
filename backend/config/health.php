<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default user id
    |--------------------------------------------------------------------------
    | Authentication is out of scope for this MVP. All records are attributed to
    | this placeholder user until real auth is introduced.
    */
    'default_user_id' => (int) env('DEFAULT_USER_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | History size
    |--------------------------------------------------------------------------
    | How many recent records the dashboard/history endpoint returns by default.
    */
    'history_limit' => 10,

    /*
    |--------------------------------------------------------------------------
    | Trend analysis
    |--------------------------------------------------------------------------
    | Minimum number of records required before a temporal trend analysis is
    | considered meaningful (the differentiator's honesty gate).
    */
    'trend_min_records' => 3,

];
