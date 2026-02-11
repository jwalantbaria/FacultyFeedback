<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class FeedbackRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function clearBySession(int $sessionId): void
    {
        $stmt = $this->db->prepare('DELETE FROM feedback WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);
    }

    public function insertBatch(int $sessionId, array $rows): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO feedback
            (session_id, enrollment_no, student_name, student_email, submitted_at, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, q11, q12, overall_score, opinion)
            VALUES
            (:session_id, :enrollment_no, :student_name, :student_email, :submitted_at, :q1, :q2, :q3, :q4, :q5, :q6, :q7, :q8, :q9, :q10, :q11, :q12, :overall_score, :opinion)'
        );

        foreach ($rows as $row) {
            $stmt->execute([
                'session_id' => $sessionId,
                'enrollment_no' => $row['enrollment_no'],
                'student_name' => $row['student_name'],
                'student_email' => $row['student_email'],
                'submitted_at' => $row['submitted_at'],
                'q1' => $row['q1'],
                'q2' => $row['q2'],
                'q3' => $row['q3'],
                'q4' => $row['q4'],
                'q5' => $row['q5'],
                'q6' => $row['q6'],
                'q7' => $row['q7'],
                'q8' => $row['q8'],
                'q9' => $row['q9'],
                'q10' => $row['q10'],
                'q11' => $row['q11'],
                'q12' => $row['q12'],
                'overall_score' => $row['overall_score'],
                'opinion' => $row['opinion'],
            ]);
        }
    }

    public function summary(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total_responses,
                    ROUND(AVG(overall_score), 2) AS average_feedback,
                    ROUND(AVG(q1), 2) AS q1_avg,
                    ROUND(AVG(q2), 2) AS q2_avg,
                    ROUND(AVG(q3), 2) AS q3_avg,
                    ROUND(AVG(q4), 2) AS q4_avg,
                    ROUND(AVG(q5), 2) AS q5_avg,
                    ROUND(AVG(q6), 2) AS q6_avg,
                    ROUND(AVG(q7), 2) AS q7_avg,
                    ROUND(AVG(q8), 2) AS q8_avg,
                    ROUND(AVG(q9), 2) AS q9_avg,
                    ROUND(AVG(q10), 2) AS q10_avg,
                    ROUND(AVG(q11), 2) AS q11_avg,
                    ROUND(AVG(q12), 2) AS q12_avg
             FROM feedback
             WHERE session_id = :session_id'
        );
        $stmt->execute(['session_id' => $sessionId]);

        return $stmt->fetch() ?: [];
    }

    public function gradeWiseAnalysis(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.grade,
                    COUNT(f.id) AS responses,
                    ROUND(AVG(f.overall_score), 2) AS avg_score
             FROM feedback f
             JOIN results r
               ON r.session_id = f.session_id
              AND r.enrollment_no = f.enrollment_no
             WHERE f.session_id = :session_id
               AND f.enrollment_no IS NOT NULL
               AND f.enrollment_no <> ""
             GROUP BY r.grade
             ORDER BY r.grade'
        );
        $stmt->execute(['session_id' => $sessionId]);

        return $stmt->fetchAll();
    }
}
