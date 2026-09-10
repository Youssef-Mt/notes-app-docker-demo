<?php

function getPdo(): PDO
{
    $host = getenv('DB_HOST') ?: 'db';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'notes';
    $user = getenv('DB_USER') ?: 'notes_app';
    $pass = getenv('DB_PASSWORD');

    if ($pass === false || $pass === '') {
        throw new RuntimeException('DB_PASSWORD manquant : à fournir via variable d\'environnement / secret Docker, jamais en dur dans le code.');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
