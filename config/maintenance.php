<?php

return [
    'enabled' => (bool) env('MAINTENANCE_ROUTE_ENABLED', false),
    'token' => (string) env('MAINTENANCE_ROUTE_TOKEN', ''),
    'expires_at' => env('MAINTENANCE_ROUTE_EXPIRES_AT'),
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('MAINTENANCE_ROUTE_ALLOWED_IPS', ''))))),
];
