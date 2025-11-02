<?php
// config/bootstrap_platform.php
declare(strict_types=1);

use Platformsh\ConfigReader\Config;

if (!class_exists(Config::class)) {
    // Paix et amour en local si lib absente
    return;
}

$config = new Config();

// Petit helper pour injecter dans l'environnement courant (process)
$setEnv = static function (string $key, string $value): void {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key.'='.$value);
};

/**
 * DATABASE_URL (MySQL)
 */
if ($config->hasRelationship('database')) {
    $db = $config->credentials('database');
    // Exemples de clés: scheme, username, password, host, port, path
    $scheme = $db['scheme'] ?? 'mysql';
    $user   = $db['username'] ?? '';
    $pass   = $db['password'] ?? '';
    $host   = $db['host'] ?? '127.0.0.1';
    $port   = (string)($db['port'] ?? 3306);
    $dbname = ltrim($db['path'] ?? '', '/');

    // Doctrine/Symfony recommande charset=utf8mb4
    $databaseUrl = sprintf('%s://%s:%s@%s:%s/%s?charset=utf8mb4',
        $scheme, rawurlencode($user), rawurlencode($pass), $host, $port, $dbname
    );
    $setEnv('DATABASE_URL', $databaseUrl);
}

/**
 * MongoDB (ODM)
 */
if ($config->hasRelationship('mongodb')) {
    $mongo = $config->credentials('mongodb');

    $user   = $mongo['username'] ?? null;
    $pass   = $mongo['password'] ?? null;
    $host   = $mongo['host'] ?? '127.0.0.1';
    $port   = (string)($mongo['port'] ?? 27017);
    $dbName = ltrim($mongo['path'] ?? 'ecoride', '/');

    // Sur Platform, l’auth utilise souvent la base "admin"
    $authSource = $mongo['query']['authSource'] ?? 'admin';

    if ($user !== null && $pass !== null) {
        $mongoUrl = sprintf(
            'mongodb://%s:%s@%s:%s/%s?authSource=%s',
            rawurlencode($user),
            rawurlencode($pass),
            $host,
            $port,
            $dbName,
            rawurlencode($authSource)
        );
    } else {
        // Sans auth (rare en prod)
        $mongoUrl = sprintf('mongodb://%s:%s/%s', $host, $port, $dbName);
    }

    $setEnv('MONGODB_URL', $mongoUrl);
    $setEnv('MONGODB_DB',  $dbName);

    // Alias attendu par certaines recettes/bundles
    $setEnv('MONGODB_URI', $mongoUrl);
}
