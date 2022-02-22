<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$cachePath = __DIR__ . '/../var/cache';

if (!\file_exists($cachePath)) {
    \mkdir($cachePath);
}

\putenv('FLOW_LOCAL_FILESYSTEM_CACHE_DIR=' . $cachePath);
