<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FeedbackRepository;
use App\Models\ReportRepository;
use App\Models\ResultRepository;

class ReportService
{
    public function __construct(
        private readonly FeedbackRepository $feedbackRepository,
        private readonly ResultRepository $resultRepository,
        private readonly ReportRepository $reportRepository
    ) {
    }

    public function generate(int $sessionId): array
    {
        $feedbackSummary = $this->feedbackRepository->summary($sessionId);
        $gradeSummary = $this->resultRepository->gradeSummary($sessionId);
        $gradeAnalysis = $this->feedbackRepository->gradeWiseAnalysis($sessionId);

        $this->reportRepository->createOrUpdate(
            $sessionId,
            (float) ($feedbackSummary['average_feedback'] ?? 0),
            (int) ($feedbackSummary['total_responses'] ?? 0),
            $gradeAnalysis
        );

        return [
            'feedback_summary' => $feedbackSummary,
            'grade_summary' => $gradeSummary,
            'grade_analysis' => $gradeAnalysis,
        ];
    }
}
