<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class SessionRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO faculty_sessions
            (academic_year, semester, subject_id, number_of_students, result_file, feedback_file)
            VALUES (:academic_year, :semester, :subject_id, :number_of_students, :result_file, :feedback_file)'
        );

        $stmt->execute([
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'],
            'subject_id' => $data['subject_id'],
            'number_of_students' => $data['number_of_students'],
            'result_file' => $data['result_file'],
            'feedback_file' => $data['feedback_file'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateFiles(int $id, ?string $resultFile, ?string $feedbackFile): void
    {
        $stmt = $this->db->prepare(
            'UPDATE faculty_sessions
             SET result_file = COALESCE(:result_file, result_file),
                 feedback_file = COALESCE(:feedback_file, feedback_file)
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'result_file' => $resultFile,
            'feedback_file' => $feedbackFile,
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT fs.*, s.name AS subject_name
             FROM faculty_sessions fs
             JOIN subjects s ON s.id = fs.subject_id
             WHERE fs.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
