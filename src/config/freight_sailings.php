<?php

return [
    'countries' => ['AE', 'SA', 'OM', 'QA', 'BH', 'KW'],
    'nearby_ports' => array_values(array_filter(array_map('trim', explode(',', (string) env('FREIGHT_NEARBY_PORTS', ''))))),
    'searates_key' => env('SEARATES_SCHEDULES_API_KEY'),
];
