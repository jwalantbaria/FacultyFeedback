<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class ResultRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function clearBySession(int $sessionId): void
    {
        $stmt = $this->db->prepare('DELETE FROM results WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);
    }

    public function insertBatch(int $sessionId, array $rows): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO results (session_id, enrollment_no, student_name, grade)
             VALUES (:session_id, :enrollment_no, :student_name, :grade)'
        );

        foreach ($rows as $row) {
            $stmt->execute([
                'session_id' => $sessionId,
                'enrollment_no' => $row['enrollment_no'],
                'student_name' => $row['student_name'],
                'grade' => $row['grade'],
            ]);
        }
    }

    public function gradeSummary(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT grade, COUNT(*) AS total_students
             FROM results
             WHERE session_id = :session_id
             GROUP BY grade
             ORDER BY grade'
        );
        $stmt->execute(['session_id' => $sessionId]);

        return $stmt->fetchAll();
    }
}
