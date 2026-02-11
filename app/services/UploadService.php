<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class UploadService
{
    public function __construct(private readonly array $config)
    {
    }

    public function upload(array $file, string $targetDir): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed.');
        }

        if (($file['size'] ?? 0) > $this->config['upload']['max_size']) {
            throw new RuntimeException('File too large. Max size is 5MB.');
        }

        $original = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->config['upload']['allowed_extensions'], true)) {
            throw new RuntimeException('Only CSV and XLSX files are allowed.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'text/plain',
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream',
        ];

        if ($mime !== false && !in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Invalid file MIME type.');
        }

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Cannot create upload directory.');
        }

        $safeName = sprintf('%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);
        $destination = rtrim($targetDir, '/') . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Could not save uploaded file.');
        }

        return $safeName;
    }
}
