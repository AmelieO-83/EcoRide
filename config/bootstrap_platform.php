<?php
// config/bootstrap_platform.php
declare(strict_types=1);

use Platformsh\ConfigReader\Config;

if (!class_exists(Config::class)) {
    return; // lib non installée en local ? on sort
}

$config = new Config();
if (!$config->isValidPlatform()) {
    return; // pas sur Platform.sh
}

$set = static function (string $k, string $v): void {
    $_ENV[$k] = $_SERVER[$k] = $v;
    putenv("$k=$v");
};

/** MySQL via relation "database" */
if ($config->hasRelationship('database')) {
    $db = $config->credentials('database');
    $set('DATABASE_URL', sprintf(
        '%s://%s:%s@%s:%d/%s?charset=utf8mb4',
        $db['scheme'] ?? 'mysql',
        $db['username'] ?? '',
        $db['password'] ?? '',
        $db['host'] ?? '127.0.0.1',
        $db['port'] ?? 3306,
        ltrim($db['path'] ?? '', '/')
    ));
}

/** MongoDB via relation "mongodb" */
if ($config->hasRelationship('mongodb')) {
    $m = $config->credentials('mongodb');
    $dbName = ltrim($m['path'] ?? 'ecoride', '/');

    $mongoUrl = isset($m['username'], $m['password'])
        ? sprintf('mongodb://%s:%s@%s:%d/%s',
            rawurlencode($m['username']),
            rawurlencode($m['password']),
            $m['host'] ?? '127.0.0.1',
            $m['port'] ?? 27017,
            $dbName
        )
        : sprintf('mongodb://%s:%d',
            $m['host'] ?? '127.0.0.1',
            $m['port'] ?? 27017
        );

    $set('MONGODB_URL', $mongoUrl);
    $set('MONGODB_DB',  $dbName);
    // Optionnel : certaines recettes lisent MONGODB_URI
    $set('MONGODB_URI', $mongoUrl);
}
