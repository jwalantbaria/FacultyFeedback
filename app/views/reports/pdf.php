<?php
$feedback = $analytics['feedback_summary'];
$grades = $analytics['grade_summary'];
$gradeFeedback = $analytics['grade_analysis'];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        h2, h3 { margin: 8px 0; }
    </style>
</head>
<body>
<h2>Faculty Feedback Report</h2>
<p><strong>Academic Year:</strong> <?= htmlspecialchars($session['academic_year']) ?></p>
<p><strong>Semester:</strong> <?= htmlspecialchars($session['semester']) ?></p>
<p><strong>Subject:</strong> <?= htmlspecialchars($session['subject_name']) ?></p>
<p><strong>Number of Students:</strong> <?= (int) $session['number_of_students'] ?></p>

<h3>Feedback Summary</h3>
<table>
    <tr><th>Average Feedback Score</th><td><?= htmlspecialchars((string) ($feedback['average_feedback'] ?? 0)) ?></td></tr>
    <tr><th>Total Responses</th><td><?= (int) ($feedback['total_responses'] ?? 0) ?></td></tr>
</table>

<h3>Question-wise Average</h3>
<table>
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

<h3>Result Summary</h3>
<table>
    <thead><tr><th>Grade</th><th>Total Students</th></tr></thead>
    <tbody>
    <?php foreach ($grades as $row): ?>
        <tr><td><?= htmlspecialchars($row['grade']) ?></td><td><?= (int) $row['total_students'] ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Grade-wise Feedback Analysis</h3>
<table>
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
</body>
</html>
