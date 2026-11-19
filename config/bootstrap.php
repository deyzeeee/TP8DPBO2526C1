<?php

declare(strict_types=1);

// Jalankan session PHP untuk kebutuhan flash message (pesan notifikasi)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Autoloader otomatis: saat PHP memanggil class atau interface baru,
 * file dengan nama yang sama akan dicari di folder models/ lalu controllers/.
 */
spl_autoload_register(function (string $className): void {
    $safeName = basename(str_replace('\\', '/', $className));
    $rootDir = dirname(__DIR__);

    $modelFile = $rootDir . '/models/' . $safeName . '.php';
    if (is_file($modelFile)) {
        require_once $modelFile;
    }

    $controllerFile = $rootDir . '/controllers/' . $safeName . '.php';
    if (is_file($controllerFile)) {
        require_once $controllerFile;
    }
});
