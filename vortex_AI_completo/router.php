<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

$segments = array_values(array_filter(explode('/', trim((string)$path, '/'))));
$apiResources = ['auth', 'pacientes', 'consultas', 'dashboard'];

if (in_array($segments[0] ?? '', $apiResources, true)) {
    require __DIR__ . '/api.php';
    return true;
}

require __DIR__ . '/index.php';
return true;
