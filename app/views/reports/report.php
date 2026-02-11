<?php
$feedback = $analytics['feedback_summary'];
$grades = $analytics['grade_summary'];
$gradeFeedback = $analytics['grade_analysis'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Faculty Feedback Report</h2>
    <a href="/?action=pdf&id=<?= (int) $session['id'] ?>" class="btn btn-outline-secondary">Export to PDF</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3 class="h5">Academic Details</h3>
        <div class="row">
            <div class="col-md-4"><strong>Academic Year:</strong> <?= htmlspecialchars($session['academic_year']) ?></div>
            <div class="col-md-4"><strong>Semester:</strong> <?= htmlspecialchars($session['semester']) ?></div>
            <div class="col-md-4"><strong>Subject:</strong> <?= htmlspecialchars($session['subject_name']) ?></div>
            <div class="col-md-4"><strong>Students:</strong> <?= (int) $session['number_of_students'] ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary"><div class="card-body"><h4 class="h6">Average Feedback</h4><p class="display-6 mb-0"><?= htmlspecialchars((string) ($feedback['average_feedback'] ?? 0)) ?></p></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success"><div class="card-body"><h4 class="h6">Total Responses</h4><p class="display-6 mb-0"><?= (int) ($feedback['total_responses'] ?? 0) ?></p></div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3 class="h5">Question-wise Averages</h3>
        <table class="table table-bordered table-sm">
            <thead><tr><th>Question</th><th>Average</th></tr></thead>
            <tbody>
            <?php foreach ($questionLabels as $qKey => $label): ?>
                <tr>
                    <td><?= htmlspecialchars(strtoupper($qKey) . ' - ' . ucfirst($label)) ?></td>
                    <td><?= htmlspecialchars((string) ($feedback[$qKey . '_avg'] ?? 0)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3 class="h5">Grade Summary</h3>
        <table class="table table-bordered">
            <thead><tr><th>Grade</th><th>Total Students</th></tr></thead>
            <tbody>
            <?php foreach ($grades as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['grade']) ?></td>
                    <td><?= (int) $row['total_students'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3 class="h5">Grade-wise Feedback Analysis (Enrollment Matched)</h3>
        <table class="table table-striped">
            <thead><tr><th>Grade</th><th>Responses</th><th>Average Score</th></tr></thead>
            <tbody>
            <?php foreach ($gradeFeedback as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['grade']) ?></td>
                    <td><?= (int) $row['responses'] ?></td>
                    <td><?= htmlspecialchars((string) $row['avg_score']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <canvas id="gradeChart" height="120"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartRows = <?= json_encode($gradeFeedback, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
new Chart(document.getElementById('gradeChart'), {
    type: 'bar',
    data: {
        labels: chartRows.map(row => row.grade),
        datasets: [{
            label: 'Average Feedback Score',
            data: chartRows.map(row => Number(row.avg_score)),
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        scales: {
            y: {beginAtZero: true, max: 5}
        }
    }
});
</script>
