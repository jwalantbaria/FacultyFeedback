<?php

declare(strict_types=1);

use App\Controllers\FacultyFeedbackController;
use App\Helpers\Database;
use App\Models\FeedbackRepository;
use App\Models\ReportRepository;
use App\Models\ResultRepository;
use App\Models\SessionRepository;
use App\Models\SubjectRepository;
use App\Services\FileParserService;
use App\Services\ReportService;
use App\Services\UploadService;

session_start();

spl_autoload_register(static function ($class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

$config = require __DIR__ . '/../app/config/config.php';
$db = Database::connection($config);

$subjectRepository = new SubjectRepository($db);
$sessionRepository = new SessionRepository($db);
$resultRepository = new ResultRepository($db);
$feedbackRepository = new FeedbackRepository($db);
$reportRepository = new ReportRepository($db);
$reportService = new ReportService($feedbackRepository, $resultRepository, $reportRepository);
$uploadService = new UploadService($config);
$fileParserService = new FileParserService();

$controller = new FacultyFeedbackController(
    $config,
    $subjectRepository,
    $sessionRepository,
    $resultRepository,
    $feedbackRepository,
    $reportService,
    $uploadService,
    $fileParserService
);

$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
        $controller->submit();
    } elseif ($action === 'report' && $id > 0) {
        $controller->showReport($id);
    } elseif ($action === 'pdf' && $id > 0) {
        $controller->exportPdf($id);
    } else {
        $controller->index();
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo '<h2>Application Error</h2>';
    echo '<p>' . htmlspecialchars($exception->getMessage()) . '</p>';
}
