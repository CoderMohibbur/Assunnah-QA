<?php

return [
    // title match debounce min chars
    'min_chars' => 4,

    // how many suggestions return
    'limit' => 5,

    // similarity threshold (0-100)
    'threshold' => 45,

    // if substring exists, force at least this score
    'substring_boost_score' => 72,

    // toast cooldown seconds (same top result)
    'toast_cooldown' => 12,
];
