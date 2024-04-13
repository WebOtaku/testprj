<?php

declare(strict_types=1);

spl_autoload_register(function ($class_name) {
    $parts = explode('\\', $class_name);
    require_once end($parts) . '.php';
});