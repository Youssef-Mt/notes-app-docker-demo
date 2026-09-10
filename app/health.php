<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

try {
    getPdo()->query('SELECT 1');
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
} catch (Throwable $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'down', 'error' => $e->getMessage()]);
}
