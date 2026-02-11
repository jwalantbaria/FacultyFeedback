<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'faculty_feedback',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'upload' => [
        'max_size' => 5 * 1024 * 1024,
        'allowed_extensions' => ['csv', 'xlsx'],
        'result_dir' => __DIR__ . '/../../public/uploads/results',
        'feedback_dir' => __DIR__ . '/../../public/uploads/feedback',
    ],
    'app' => [
        'name' => 'Faculty Feedback Report Generator',
        'base_url' => getenv('BASE_URL') ?: '',
    ],
];
