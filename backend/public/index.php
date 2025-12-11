<?php

declare(strict_types=1);

$router = require __DIR__ . '/bootstrap.php';
$router->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
