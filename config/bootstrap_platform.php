<?php
// config/bootstrap_platform.php
// Mappe PLATFORM_RELATIONSHIPS -> MONGODB_URL / MONGODB_DB pour Symfony (Platform.sh)

$rels = getenv('PLATFORM_RELATIONSHIPS');
if (!$rels) {
    // Pas sur Platform.sh : ne rien faire
    return;
}

$data = json_decode(base64_decode($rels), true);
if (!is_array($data) || empty($data['mongodb'][0])) {
    return;
}

$mongo = $data['mongodb'][0];

$username = $mongo['username'] ?? null;
$password = $mongo['password'] ?? null;
$host     = $mongo['host']     ?? 'localhost';
$port     = $mongo['port']     ?? 27017;
$path     = $mongo['path']     ?? '';
$authDb   = ltrim($path, '/') ?: 'admin';

// Construit l’URI Mongo
if ($username && $password) {
    $uri = sprintf(
        'mongodb://%s:%s@%s:%d/?authSource=%s',
        rawurlencode($username),
        rawurlencode($password),
        $host,
        $port,
        $authDb
    );
} else {
    $uri = sprintf('mongodb://%s:%d', $host, $port);
}

// Base par défaut = path sans le "/" (fallback)
$db = ltrim($path, '/') ?: 'ecoride';

// Expose pour doctrine_mongodb.yaml
putenv('MONGODB_URL=' . $uri);
$_ENV['MONGODB_URL'] = $uri;
$_SERVER['MONGODB_URL'] = $uri;

putenv('MONGODB_DB=' . $db);
$_ENV['MONGODB_DB'] = $db;
$_SERVER['MONGODB_DB'] = $db;
