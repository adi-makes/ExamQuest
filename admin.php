<?php
require_once 'db.php';

// Fetch dropdown data
$syllabi_result  = mysqli_query($conn, 'SELECT syllabus_id, regulation_year FROM syllabus ORDER BY syllabus_id ASC');
$branches_result = mysqli_query($conn, 'SELECT branch_id, branch_name FROM branch ORDER BY branch_name ASC');

$syllabi  = mysqli_fetch_all($syllabi_result,  MYSQLI_ASSOC);
$branches = mysqli_fetch_all($branches_result, MYSQLI_ASSOC);

// Fetch ALL subjects for JS cascade (dump as JSON)
$all_subjects_result = mysqli_query($conn,
    'SELECT subject_id, subject_name, syllabus_id, branch_id, semester FROM subject ORDER BY subject_name ASC'
);
$all_subjects = mysqli_fetch_all($all_subjects_result, MYSQLI_ASSOC);
// Cast numeric IDs to int; keep semester as string for JS comparison
foreach ($all_subjects as &$s) {
    $s['subject_id']  = intval($s['subject_id']);
    $s['syllabus_id'] = intval($s['syllabus_id']);
    $s['branch_id']   = intval($s['branch_id']);
}
unset($s);

// Stats
$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions');
$stmt->execute();
$total_questions = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions WHERE frequency >= 5');
$stmt->execute();
$flagged_count = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM subject');
$stmt->execute();
$total_subjects = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

// Handle POST form submission
$errors     = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $subject_id    = intval($_POST['subject_id']    ?? 0);
    $question_text = trim($_POST['question_text']   ?? '');
    $marks         = intval($_POST['marks']         ?? 0);
    $frequency     = intval($_POST['frequency']     ?? 0);

    // Validate
    if ($subject_id <= 0)           $errors[] = 'Please select a valid subject.';
    if (strlen($question_text) < 5) $errors[] = 'Question text must be at least 5 characters.';
    if ($marks <= 0)                $errors[] = 'Marks must be greater than 0.';
    if ($marks > 100)               $errors[] = 'Marks cannot exceed 100.';
    if ($frequency < 0)             $errors[] = 'Frequency count cannot be negative.';

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isii', $subject_id, $question_text, $marks, $frequency);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: admin.php?success=1');
            exit;
        } else {
            $errors[] = 'Database error. Please try again.';
            $stmt->close();
        }
    }
}

// Flash message from PRG redirect
if (isset($_GET['success'])) {
    $success_msg = 'Question added successfully';
    // Refresh stats after redirect
    $stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions');
    $stmt->execute();
    $total_questions = intval($stmt->get_result()->fetch_assoc()['cnt']);
    $stmt->close();

    $stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions WHERE frequency >= 5');
    $stmt->execute();
    $flagged_count = intval($stmt->get_result()->fetch_assoc()['cnt']);
    $stmt->close();

    $stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM subject');
    $stmt->execute();
    $total_subjects = intval($stmt->get_result()->fetch_assoc()['cnt']);
    $stmt->close();
}

// Restore POST values for sticky form (on validation error)
$form = [
    'syllabus_id'    => intval($_POST['syllabus_id']  ?? 0),
    'branch_id'      => intval($_POST['branch_id']    ?? 0),
    'semester'       => trim($_POST['semester']        ?? ''),
    'subject_id'     => intval($_POST['subject_id']   ?? 0),
    'question_text'  => $_POST['question_text']       ?? '',
    'marks'          => $_POST['marks']               ?? '',
    'frequency'      => $_POST['frequency']            ?? '',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – ExamQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation -->
<nav class="nav">
    <div class="nav__inner">
        <a href="index.php" class="nav__brand">ExamQuest</a>
        <div class="nav__links">
            <a href="index.php">Home</a>
            <a href="subject.php">Subjects</a>
            <a href="admin.php" class="active">Admin</a>
            <a href="about.php">About</a>
        </div>
    </div>
</nav>

<main>
    <div class="container container--narrow" style="padding-bottom:48px;">

        <!-- Page Header -->
        <div class="page-header page-header--centered">
            <h1>Admin Dashboard – Add Question</h1>
            <p>Insert new questions into the exam question database</p>
        </div>

        <!-- Alerts -->
        <?php if ($success_msg): ?>
        <div class="alert alert--success">
            <span class="alert__icon">✓</span>
            <?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert--error" style="flex-direction:column;align-items:flex-start;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <span class="alert__icon">!</span>
                <strong>Please fix the following:</strong>
            </div>
            <ul style="list-style:disc;padding-left:32px;margin:0;">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Admin Form -->
        <div class="card admin-form">
            <form method="POST" action="admin.php">

                <!-- Row 1: Syllabus | Branch -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="adminSyllabus">Select Syllabus</label>
                        <select name="syllabus_id" id="adminSyllabus" class="input input--select" required>
                            <option value="">-- Choose Syllabus --</option>
                            <?php foreach ($syllabi as $s): ?>
                            <option value="<?= $s['syllabus_id'] ?>"
                                <?= $form['syllabus_id'] == $s['syllabus_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['regulation_year'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="adminBranch">Select Branch</label>
                        <select name="branch_id" id="adminBranch" class="input input--select" required>
                            <option value="">-- Choose Branch --</option>
                            <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['branch_id'] ?>"
                                <?= $form['branch_id'] == $b['branch_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['branch_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Semester | Subject -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="adminSemester">Select Semester</label>
                        <select name="semester" id="adminSemester" class="input input--select" required>
                            <option value="">-- Choose Semester --</option>
                            <?php foreach (range(1, 8) as $s): ?>
                            <option value="<?= $s ?>"
                                <?= $form['semester'] == $s ? 'selected' : '' ?>>
                                Semester <?= $s ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="adminSubject">Select Subject</label>
                        <select name="subject_id" id="adminSubject" class="input input--select" required>
                            <option value="">-- Select Subject --</option>
                            <?php if ($form['subject_id'] > 0): ?>
                            <?php foreach ($all_subjects as $subj): ?>
                            <?php if ($subj['subject_id'] === $form['subject_id']): ?>
                            <option value="<?= $subj['subject_id'] ?>" selected>
                                <?= htmlspecialchars($subj['subject_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="color:var(--color-secondary-text);font-size:12px;margin-top:4px;">
                            Select syllabus, branch, and semester first to load subjects.
                        </small>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="form-group">
                    <label class="form-label" for="questionText">Enter Question Text</label>
                    <textarea name="question_text" id="questionText" class="input input--textarea"
                              placeholder="Describe the question in detail. Use clear academic language."
                              required><?= htmlspecialchars($form['question_text'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Row 3: Marks | Frequency -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="marks">Enter Marks</label>
                        <div class="input-with-icon">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </span>
                            <input type="number" name="marks" id="marks" class="input"
                                   placeholder="e.g. 10" min="1" max="100"
                                   value="<?= htmlspecialchars($form['marks'], ENT_QUOTES, 'UTF-8') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="frequency">Enter Frequency Count</label>
                        <div class="input-with-icon">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                            </span>
                            <input type="number" name="frequency" id="frequency" class="input"
                                   placeholder="How many times appeared?" min="0"
                                   value="<?= htmlspecialchars($form['frequency'], ENT_QUOTES, 'UTF-8') ?>"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" name="add_question" class="btn btn--primary btn--full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Question to Database
                    </button>
                    <button type="reset" class="btn btn--secondary" id="resetBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Add Another Question
                    </button>
                </div>

            </form>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">&nbsp;</span>
                </div>
                <div class="stat-card__value"><?= number_format($total_questions) ?></div>
                <div class="stat-card__label">Total Questions</div>
            </div>

            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon" style="background:#FEF3C7;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">&nbsp;</span>
                </div>
                <div class="stat-card__value"><?= number_format($flagged_count) ?></div>
                <div class="stat-card__label">Flagged Questions</div>
            </div>

            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">&nbsp;</span>
                </div>
                <div class="stat-card__value"><?= number_format($total_subjects) ?></div>
                <div class="stat-card__label">Total Subjects</div>
            </div>
        </div>

    </div>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="footer__brand">ExamQuest – Smart Question Bank Explorer</div>
    <div class="footer__tagline">Developed as part of DBMS Mini Project</div>
    <div class="footer__links">
        <a href="#">Help Desk</a>
        <a href="#">Privacy</a>
    </div>
</footer>

<script>
// All subjects data for cascading dropdown
const ALL_SUBJECTS = <?= json_encode($all_subjects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

const adminSyllabus  = document.getElementById('adminSyllabus');
const adminBranch    = document.getElementById('adminBranch');
const adminSemester  = document.getElementById('adminSemester');
const adminSubject   = document.getElementById('adminSubject');
const savedSubject   = <?= intval($form['subject_id']) ?>;

function updateSubjects() {
    const syl = parseInt(adminSyllabus.value) || 0;
    const br  = parseInt(adminBranch.value)   || 0;
    const sem = adminSemester.value;

    adminSubject.innerHTML = '<option value="">-- Select Subject --</option>';

    if (syl === 0 || br === 0 || sem === '') return;

    const filtered = ALL_SUBJECTS.filter(function(s) {
        return s.syllabus_id === syl && s.branch_id === br && s.semester === sem;
    });

    filtered.forEach(function(s) {
        const opt = document.createElement('option');
        opt.value = s.subject_id;
        opt.textContent = s.subject_name;
        if (s.subject_id === savedSubject) opt.selected = true;
        adminSubject.appendChild(opt);
    });

    if (filtered.length === 0) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'No subjects found for this combination';
        opt.disabled = true;
        adminSubject.appendChild(opt);
    }
}

// Update subjects when any filter changes
[adminSyllabus, adminBranch, adminSemester].forEach(function(el) {
    el.addEventListener('change', updateSubjects);
});

// Restore subjects if form was returned with errors
if (<?= $form['syllabus_id'] ?> > 0 && <?= $form['branch_id'] ?> > 0 && <?= json_encode($form['semester']) ?> !== '') {
    updateSubjects();
}

// Reset button — also clear the subject dropdown hint
document.getElementById('resetBtn').addEventListener('click', function() {
    setTimeout(function() {
        adminSubject.innerHTML = '<option value="">-- Select Subject --</option>';
    }, 10);
});
</script>

</body>
</html>
