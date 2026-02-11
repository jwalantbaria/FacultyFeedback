<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class SubjectRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function findOrCreate(string $name): int
    {
        $stmt = $this->db->prepare('SELECT id FROM subjects WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $found = $stmt->fetch();

        if ($found) {
            return (int) $found['id'];
        }

        $insert = $this->db->prepare('INSERT INTO subjects (name) VALUES (:name)');
        $insert->execute(['name' => $name]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query('SELECT id, name FROM subjects ORDER BY name')->fetchAll();
    }
}
