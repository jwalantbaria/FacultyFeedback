<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\FeedbackRepository;
use App\Models\ResultRepository;
use App\Models\SessionRepository;
use App\Models\SubjectRepository;
use App\Services\FileParserService;
use App\Services\ReportService;
use App\Services\UploadService;
use Dompdf\Dompdf;
use RuntimeException;

class FacultyFeedbackController
{
    private const QUESTION_KEYWORDS = [
        'q1' => 'syllabus coverage',
        'q2' => 'beyond syllabus',
        'q3' => 'technical content',
        'q4' => 'communication skills',
        'q5' => 'teaching aids',
        'q6' => 'pace',
        'q7' => 'motivation',
        'q8' => 'practical demonstration',
        'q9' => 'hands on training',
        'q10' => 'level of satisfying expectations',
        'q11' => 'feedback provided on students progress',
        'q12' => 'offer help and advice',
    ];

    public function __construct(
        private readonly array $config,
        private readonly SubjectRepository $subjectRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly ResultRepository $resultRepository,
        private readonly FeedbackRepository $feedbackRepository,
        private readonly ReportService $reportService,
        private readonly UploadService $uploadService,
        private readonly FileParserService $fileParserService
    ) {
    }

    public function index(): void
    {
        $this->render('reports/form', [
            'subjects' => $this->subjectRepository->all(),
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? [],
        ]);

        unset($_SESSION['errors'], $_SESSION['old']);
    }

    public function submit(): void
    {
        try {
            $data = $this->validateForm($_POST);

            $subjectId = $data['subject_id'] ?: $this->subjectRepository->findOrCreate($data['subject_name']);

            $resultFile = $this->uploadService->upload($_FILES['result_file'], $this->config['upload']['result_dir']);
            $feedbackFile = $this->uploadService->upload($_FILES['feedback_file'], $this->config['upload']['feedback_dir']);

            $sessionId = $this->sessionRepository->create([
                'academic_year' => $data['academic_year'],
                'semester' => $data['semester'],
                'subject_id' => $subjectId,
                'number_of_students' => $data['number_of_students'],
                'result_file' => $resultFile,
                'feedback_file' => $feedbackFile,
            ]);

            $this->storeResults($sessionId, $this->config['upload']['result_dir'] . '/' . $resultFile);
            $this->storeFeedback($sessionId, $this->config['upload']['feedback_dir'] . '/' . $feedbackFile);

            $this->reportService->generate($sessionId);

            header('Location: /?action=report&id=' . $sessionId);
            exit;
        } catch (RuntimeException $exception) {
            $_SESSION['errors'] = [$exception->getMessage()];
            $_SESSION['old'] = $_POST;
            header('Location: /');
            exit;
        }
    }

    public function showReport(int $sessionId): void
    {
        $session = $this->sessionRepository->find($sessionId);
        if (!$session) {
            $_SESSION['errors'] = ['Session not found.'];
            header('Location: /');
            exit;
        }

        $analytics = $this->reportService->generate($sessionId);

        $this->render('reports/report', [
            'session' => $session,
            'analytics' => $analytics,
            'questionLabels' => self::QUESTION_KEYWORDS,
        ]);
    }

    public function exportPdf(int $sessionId): void
    {
        $session = $this->sessionRepository->find($sessionId);
        if (!$session) {
            throw new RuntimeException('Session not found.');
        }

        $analytics = $this->reportService->generate($sessionId);
        $html = $this->viewToString('reports/pdf', [
            'session' => $session,
            'analytics' => $analytics,
            'questionLabels' => self::QUESTION_KEYWORDS,
        ]);

        if (!class_exists(Dompdf::class)) {
            throw new RuntimeException('Dompdf not installed. Run: composer require dompdf/dompdf');
        }

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('faculty-feedback-report-' . $sessionId . '.pdf', ['Attachment' => true]);
    }

    private function validateForm(array $input): array
    {
        $academicYear = trim($input['academic_year'] ?? '');
        $semester = trim($input['semester'] ?? '');
        $subjectName = trim($input['subject_name'] ?? '');
        $subjectId = isset($input['subject_id']) && ctype_digit((string) $input['subject_id']) ? (int) $input['subject_id'] : null;
        $numberOfStudents = filter_var($input['number_of_students'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 1000],
        ]);

        if ($academicYear === '' || $semester === '' || ($subjectName === '' && !$subjectId) || $numberOfStudents === false) {
            throw new RuntimeException('Please provide valid academic details.');
        }

        if (!isset($_FILES['result_file'], $_FILES['feedback_file'])) {
            throw new RuntimeException('Both files are required.');
        }

        return [
            'academic_year' => $academicYear,
            'semester' => $semester,
            'subject_name' => $subjectName,
            'subject_id' => $subjectId,
            'number_of_students' => $numberOfStudents,
        ];
    }

    private function storeResults(int $sessionId, string $filePath): void
    {
        $rows = $this->fileParserService->parse($filePath);
        $header = array_map('strtolower', $rows[0] ?? []);
        $required = ['enrollment no', 'student name', 'grade'];
        foreach ($required as $column) {
            if (!in_array($column, $header, true)) {
                throw new RuntimeException('Result file missing column: ' . $column);
            }
        }

        $idx = array_flip($header);
        $payload = [];
        foreach (array_slice($rows, 1) as $row) {
            if (($row[$idx['enrollment no']] ?? '') === '') {
                continue;
            }
            $payload[] = [
                'enrollment_no' => (string) ($row[$idx['enrollment no']] ?? ''),
                'student_name' => (string) ($row[$idx['student name']] ?? ''),
                'grade' => strtoupper((string) ($row[$idx['grade']] ?? 'NA')),
            ];
        }

        $this->resultRepository->clearBySession($sessionId);
        $this->resultRepository->insertBatch($sessionId, $payload);
    }

    private function storeFeedback(int $sessionId, string $filePath): void
    {
        $rows = $this->fileParserService->parse($filePath);
        if (count($rows) < 2) {
            throw new RuntimeException('Feedback file has no data rows.');
        }

        $headerMap = $this->mapFeedbackHeader($rows[0]);

        foreach (array_keys(self::QUESTION_KEYWORDS) as $qKey) {
            if (!isset($headerMap[$qKey])) {
                throw new RuntimeException('Feedback file missing rating column: ' . $qKey);
            }
        }

        $payload = [];
        foreach (array_slice($rows, 1) as $row) {
            $scores = [];
            foreach (array_keys(self::QUESTION_KEYWORDS) as $qKey) {
                $scores[$qKey] = $this->extractScore((string) ($row[$headerMap[$qKey]] ?? '0'));
            }

            if (array_sum($scores) <= 0) {
                continue;
            }

            $payload[] = [
                'enrollment_no' => isset($headerMap['enrollment']) ? trim((string) ($row[$headerMap['enrollment']] ?? '')) ?: null : null,
                'student_name' => isset($headerMap['student_name']) ? trim((string) ($row[$headerMap['student_name']] ?? '')) ?: null : null,
                'student_email' => isset($headerMap['email']) ? trim((string) ($row[$headerMap['email']] ?? '')) ?: null : null,
                'submitted_at' => isset($headerMap['timestamp']) ? trim((string) ($row[$headerMap['timestamp']] ?? '')) ?: null : null,
                'q1' => $scores['q1'],
                'q2' => $scores['q2'],
                'q3' => $scores['q3'],
                'q4' => $scores['q4'],
                'q5' => $scores['q5'],
                'q6' => $scores['q6'],
                'q7' => $scores['q7'],
                'q8' => $scores['q8'],
                'q9' => $scores['q9'],
                'q10' => $scores['q10'],
                'q11' => $scores['q11'],
                'q12' => $scores['q12'],
                'overall_score' => round(array_sum($scores) / count($scores), 2),
                'opinion' => isset($headerMap['opinion']) ? trim((string) ($row[$headerMap['opinion']] ?? '')) ?: null : null,
            ];
        }

        if ($payload === []) {
            throw new RuntimeException('No valid feedback responses found to import.');
        }

        $this->feedbackRepository->clearBySession($sessionId);
        $this->feedbackRepository->insertBatch($sessionId, $payload);
    }

    private function mapFeedbackHeader(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $index => $columnRaw) {
            $column = strtolower(trim((string) $columnRaw));

            if ($column === '') {
                continue;
            }

            if (str_contains($column, 'timestamp')) {
                $map['timestamp'] = $index;
                continue;
            }

            if (str_contains($column, 'email address')) {
                $map['email'] = $index;
                continue;
            }

            if (str_contains($column, 'student') && str_contains($column, 'name')) {
                $map['student_name'] = $index;
                continue;
            }

            if (str_contains($column, 'enrollment')) {
                $map['enrollment'] = $index;
                continue;
            }

            if (str_contains($column, 'opinion regarding me and course') || str_contains($column, 'please do write an opinion')) {
                $map['opinion'] = $index;
                continue;
            }

            foreach (self::QUESTION_KEYWORDS as $qKey => $keyword) {
                if ($column === $qKey || str_contains($column, $keyword)) {
                    $map[$qKey] = $index;
                }
            }
        }

        return $map;
    }

    private function extractScore(string $raw): float
    {
        if (preg_match('/([1-5](?:\.0+)?)/', $raw, $matches) !== 1) {
            return 0.0;
        }

        $score = (float) $matches[1];

        return ($score >= 1 && $score <= 5) ? $score : 0.0;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $config = $this->config;
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/' . $view . '.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    private function viewToString(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include __DIR__ . '/../views/' . $view . '.php';

        return (string) ob_get_clean();
    }
}
