<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Faculty Feedback Input</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="/?action=submit" method="post" enctype="multipart/form-data" class="row g-3" novalidate>
                    <div class="col-md-6">
                        <label class="form-label">Academic Year</label>
                        <select class="form-select" name="academic_year" required>
                            <option value="">Select Academic Year</option>
                            <?php foreach (['2023-24', '2024-25', '2025-26'] as $year): ?>
                                <option value="<?= $year ?>" <?= (($old['academic_year'] ?? '') === $year) ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester" required>
                            <option value="">Select Semester</option>
                            <?php foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'] as $sem): ?>
                                <option value="<?= $sem ?>" <?= (($old['semester'] ?? '') === $sem) ? 'selected' : '' ?>><?= $sem ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subject (Existing)</label>
                        <select class="form-select" name="subject_id">
                            <option value="">Choose Existing Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= (int) $subject['id'] ?>" <?= ((int) ($old['subject_id'] ?? 0) === (int) $subject['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Or Enter New Subject</label>
                        <input type="text" class="form-control" name="subject_name" value="<?= htmlspecialchars($old['subject_name'] ?? '') ?>" placeholder="e.g. Database Management Systems">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Number of Students</label>
                        <input type="number" class="form-control" name="number_of_students" min="1" max="1000" required value="<?= htmlspecialchars($old['number_of_students'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Result File (CSV/XLSX)</label>
                        <input type="file" class="form-control" name="result_file" accept=".csv,.xlsx" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Feedback File (CSV/XLSX)</label>
                        <input type="file" class="form-control" name="feedback_file" accept=".csv,.xlsx" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Generate Feedback Report</button>
                    </div>
                </form>

                <hr>
                <h3 class="h6">Expected File Headers</h3>
                <ul>
                    <li><strong>Results:</strong> Enrollment No, Student Name, Grade</li>
                    <li><strong>Feedback:</strong> Supports Google Form export with columns like Timestamp, Email Address, Student Name, Enrollment No and all 12 question statements.</li>
                    <li><strong>Also Supported:</strong> Simplified format with <code>Q1</code> to <code>Q12</code> columns.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
