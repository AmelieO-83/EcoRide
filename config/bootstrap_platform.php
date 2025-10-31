<?php
// config/bootstrap_platform.php

declare(strict_types=1);

/**
 * Lorsqu'on tourne sur Platform.sh, PLATFORMSH_* sont présents.
 * On lit les relations et on expose DATABASE_URL, MONGODB_URL, MONGODB_DB
 * pour que Symfony/Doctrine les trouvent pendant cache:clear.
 */
if (!getenv('PLATFORM_RELATIONSHIPS')) {
    return; // pas sur Platform.sh -> on ne fait rien
}

$relationships = json_decode(base64_decode(getenv('PLATFORM_RELATIONSHIPS')), true);
if (!is_array($relationships)) {
    return;
}

$setEnv = static function (string $key, string $value): void {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key.'='.$value);
};

// ----- MySQL (relation: database) -----
if (!empty($relationships['database'][0])) {
    $db = $relationships['database'][0];
    // Ex: scheme=mysql, username, password, host, port, path (dbname)
    $scheme = $db['scheme'] ?? 'mysql';
    $user   = $db['username'] ?? '';
    $pass   = $db['password'] ?? '';
    $host   = $db['host'] ?? '127.0.0.1';
    $port   = $db['port'] ?? 3306;
    $dbname = ltrim($db['path'] ?? '', '/');

    // Charset recommandé pour Doctrine/Symfony
    $databaseUrl = sprintf('%s://%s:%s@%s:%s/%s?charset=utf8mb4', $scheme, $user, $pass, $host, $port, $dbname);
    $setEnv('DATABASE_URL', $databaseUrl);
}

// ----- MongoDB (relation: mongodb) -----
if (!empty($relationships['mongodb'][0])) {
    $mongo = $relationships['mongodb'][0];
    $user   = $mongo['username'] ?? null;
    $pass   = $mongo['password'] ?? null;
    $host   = $mongo['host'] ?? '127.0.0.1';
    $port   = $mongo['port'] ?? 27017;
    $dbName = ltrim($mongo['path'] ?? 'ecoride', '/');

    // Construire une URL standard MongoDB
    if ($user !== null && $pass !== null) {
        $mongoUrl = sprintf('mongodb://%s:%s@%s:%s/%s', rawurlencode($user), rawurlencode($pass), $host, $port, $dbName);
    } else {
        $mongoUrl = sprintf('mongodb://%s:%s', $host, $port);
    }

    $setEnv('MONGODB_URL', $mongoUrl);
    $setEnv('MONGODB_DB', $dbName);
}
