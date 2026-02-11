<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class ReportRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function createOrUpdate(int $sessionId, float $avgFeedback, int $responses, array $gradeAnalysis): void
    {
        $payload = json_encode($gradeAnalysis, JSON_THROW_ON_ERROR);

        $stmt = $this->db->prepare(
            'INSERT INTO reports (session_id, average_feedback_score, total_responses, grade_analysis_json)
             VALUES (:session_id, :average_feedback_score, :total_responses, :grade_analysis_json)
             ON DUPLICATE KEY UPDATE
                average_feedback_score = VALUES(average_feedback_score),
                total_responses = VALUES(total_responses),
                grade_analysis_json = VALUES(grade_analysis_json)'
        );

        $stmt->execute([
            'session_id' => $sessionId,
            'average_feedback_score' => $avgFeedback,
            'total_responses' => $responses,
            'grade_analysis_json' => $payload,
        ]);
    }
}
