<?php
/**
 * admin.php — Admin Dashboard (Add Question)
 *
 * A simple internal form for inserting new questions into the database.
 * Implements the Post-Redirect-Get (PRG) pattern:
 *   1. GET  — render the form with current stats
 *   2. POST — validate input → INSERT → redirect to ?success=1
 *   3. GET  — re-render the form with a success flash message
 *
 * The cascading Subject dropdown is powered by a small JS function
 * (updateSubjects) that filters a JSON array embedded in the page,
 * rather than making extra AJAX requests.
 */

require_once 'db.php';

// ── Dropdown data ─────────────────────────────────────────────────────────────
// Load syllabi and branches for the first two dropdowns.
// mysqli_fetch_all() retrieves all rows at once into a PHP array so we can
// pass them to the template and also embed them in JSON for JavaScript.

$syllabi_result  = mysqli_query($conn, 'SELECT syllabus_id, regulation_year FROM syllabus ORDER BY syllabus_id ASC');
$branches_result = mysqli_query($conn, 'SELECT branch_id, branch_name FROM branch ORDER BY branch_name ASC');

$syllabi  = mysqli_fetch_all($syllabi_result,  MYSQLI_ASSOC);
$branches = mysqli_fetch_all($branches_result, MYSQLI_ASSOC);

// Load ALL subjects at once for the JavaScript cascade.
// The JS function filters this array client-side instead of requesting
// a new page from the server every time the user changes a dropdown.
$all_subjects_result = mysqli_query($conn,
    'SELECT subject_id, subject_name, syllabus_id, branch_id, semester FROM subject ORDER BY subject_name ASC'
);
$all_subjects = mysqli_fetch_all($all_subjects_result, MYSQLI_ASSOC);

// Cast numeric ID columns to int so JavaScript strict comparison (===) works.
// MySQLi returns all values as strings by default; "3" === 3 is false in JS.
foreach ($all_subjects as &$s) {
    $s['subject_id']  = intval($s['subject_id']);
    $s['syllabus_id'] = intval($s['syllabus_id']);
    $s['branch_id']   = intval($s['branch_id']);
    // semester is kept as a string ("3", "4") to match the JS select value
}
unset($s); // break the reference to avoid accidental mutation later

// ── Handle POST — Insert question (PRG pattern) ───────────────────────────────
// We check for the named submit button ('add_question') so regular page
// refreshes or direct GETs don't trigger this block.

$errors      = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {

    // Read and sanitise form fields
    $subject_id    = intval($_POST['subject_id']  ?? 0);
    $question_text = trim($_POST['question_text'] ?? '');
    $marks         = intval($_POST['marks']       ?? 0);
    $frequency     = intval($_POST['frequency']   ?? 0);

    // Server-side validation (HTML `required` attributes are client-side only
    // and can be bypassed, so we always validate on the server as well)
    if ($subject_id <= 0)           $errors[] = 'Please select a valid subject.';
    if (strlen($question_text) < 5) $errors[] = 'Question text must be at least 5 characters.';
    if ($marks <= 0)                $errors[] = 'Marks must be greater than 0.';
    if ($marks > 100)               $errors[] = 'Marks cannot exceed 100.';
    if ($frequency < 0)             $errors[] = 'Frequency count cannot be negative.';

    if (empty($errors)) {
        // Use a prepared statement to prevent SQL injection.
        // The `?` placeholders are bound separately from the query text,
        // so user input is never treated as SQL syntax.
        // bind_param types: i=integer, s=string, i=integer, i=integer
        $stmt = $conn->prepare('
            INSERT INTO questions (subject_id, question_text, marks, frequency)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->bind_param('isii', $subject_id, $question_text, $marks, $frequency);

        if ($stmt->execute()) {
            $stmt->close();
            // PRG: redirect after a successful INSERT so hitting F5 doesn't
            // re-submit the form and insert a duplicate row.
            header('Location: admin.php?success=1');
            exit;
        } else {
            $errors[] = 'Database error. Please try again.';
            $stmt->close();
        }
    }
}

// ── Flash message (set when redirected back after success) ────────────────────
if (isset($_GET['success'])) {
    $success_msg = 'Question added successfully';
}

// ── Load live stats ───────────────────────────────────────────────────────────
// Loaded after POST handling so the numbers always reflect the latest state,
// including any question just inserted.

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions');
$stmt->execute();
$total_questions = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions WHERE frequency >= 5');
$stmt->execute();
$flagged_count = intval($stmt->get_result()->fetch_assoc()['cnt']); // high-priority questions
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM subject');
$stmt->execute();
$total_subjects = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

// ── Sticky form values ────────────────────────────────────────────────────────
// If the form was submitted with validation errors, we re-render it with the
// user's original input so they don't have to retype everything.
// On a clean GET (or after redirect), all values default to empty / zero.

$form = [
    'syllabus_id'   => intval($_POST['syllabus_id']  ?? 0),
    'branch_id'     => intval($_POST['branch_id']    ?? 0),
    'semester'      => trim($_POST['semester']        ?? ''),
    'subject_id'    => intval($_POST['subject_id']   ?? 0),
    'question_text' => $_POST['question_text']       ?? '',
    'marks'         => $_POST['marks']               ?? '',
    'frequency'     => $_POST['frequency']           ?? '',
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

<!-- ── Navigation bar ─────────────────────────────────────────────────────── -->
<nav class="nav">
    <div class="nav__inner">
        <a href="index.php" class="nav__brand">ExamQuest</a>
        <div class="nav__links">
            <a href="index.php">Home</a>
        </div>
    </div>
</nav>

<main>
    <div class="container container--narrow" style="padding-bottom:48px;">

        <!-- ── Page header ────────────────────────────────────────────────── -->
        <div class="page-header page-header--centered">
            <h1>Admin Dashboard – Add Question</h1>
            <p>Insert new questions into the exam question database</p>
        </div>

        <!-- ── Alerts ─────────────────────────────────────────────────────── -->
        <!-- Success flash: shown once after a PRG redirect with ?success=1 -->
        <?php if ($success_msg): ?>
        <div class="alert alert--success">
            <span class="alert__icon">✓</span>
            <?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <!-- Validation errors: shown when the server rejected the POST -->
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

        <!-- ── Add Question form ──────────────────────────────────────────── -->
        <!-- POSTs to itself; PHP at the top handles the insert and redirect -->
        <div class="card admin-form">
            <form method="POST" action="admin.php">

                <!-- Row 1: Syllabus | Branch ─────────────────────────────── -->
                <!-- These two dropdowns narrow down the subject list for Row 2. -->
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

                <!-- Row 2: Semester | Subject ───────────────────────────── -->
                <!-- The Subject dropdown is empty by default; JS populates it
                     once Syllabus + Branch + Semester are all selected. -->
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
                            <?php
                            // If the form had a validation error and a subject was already chosen,
                            // pre-render that option so it stays selected after the page reloads.
                            if ($form['subject_id'] > 0):
                                foreach ($all_subjects as $subj):
                                    if ($subj['subject_id'] === $form['subject_id']): ?>
                            <option value="<?= $subj['subject_id'] ?>" selected>
                                <?= htmlspecialchars($subj['subject_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php       endif;
                                endforeach;
                            endif; ?>
                        </select>
                        <small style="color:var(--color-secondary-text);font-size:12px;margin-top:4px;">
                            Select syllabus, branch, and semester first to load subjects.
                        </small>
                    </div>
                </div>

                <!-- Question text ────────────────────────────────────────── -->
                <div class="form-group">
                    <label class="form-label" for="questionText">Enter Question Text</label>
                    <textarea name="question_text" id="questionText" class="input input--textarea"
                              placeholder="Describe the question in detail. Use clear academic language."
                              required><?= htmlspecialchars($form['question_text'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Row 3: Marks | Frequency ─────────────────────────────── -->
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
                        <!-- Frequency = how many past exam papers this question appeared in -->
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

                <!-- Action buttons ───────────────────────────────────────── -->
                <div class="form-actions">
                    <!-- Named submit button — PHP checks isset($_POST['add_question']) -->
                    <button type="submit" name="add_question" class="btn btn--primary btn--full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Question to Database
                    </button>
                    <!-- Reset clears all HTML inputs; JS also resets the subject dropdown -->
                    <button type="reset" class="btn btn--secondary" id="resetBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Add Another Question
                    </button>
                </div>

            </form>
        </div>

        <!-- ── Live stats row ─────────────────────────────────────────────── -->
        <!-- Refreshed on every page load (including after the success redirect). -->
        <div class="stats-row">

            <!-- Total questions in the entire database -->
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

            <!-- Questions with frequency >= 5 (flagged as HIGH PRIORITY) -->
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

            <!-- Total number of subjects in the DB -->
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

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="footer">
    <div class="footer__brand">ExamQuest – Smart Question Bank Explorer</div>
    <div class="footer__tagline">Developed as part of DBMS Mini Project</div>
    <div class="footer__links">
        <a href="#">Help Desk</a>
        <a href="#">Privacy</a>
    </div>
</footer>

<!-- ── Cascading subject dropdown (JavaScript) ────────────────────────────── -->
<script>
// ALL_SUBJECTS is the full subjects array, JSON-encoded by PHP and embedded
// directly into the page. json_encode flags prevent XSS in attribute contexts.
const ALL_SUBJECTS = <?= json_encode($all_subjects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

// Cache DOM references — reading these once is faster than querying each time
const adminSyllabus = document.getElementById('adminSyllabus');
const adminBranch   = document.getElementById('adminBranch');
const adminSemester = document.getElementById('adminSemester');
const adminSubject  = document.getElementById('adminSubject');

// savedSubject holds the subject_id from the last failed POST (0 = none).
// Used to re-select the correct option after JS re-populates the dropdown.
const savedSubject = <?= intval($form['subject_id']) ?>;

/**
 * updateSubjects()
 * Reads the current values of the three filter dropdowns, filters ALL_SUBJECTS
 * to matching entries, and rebuilds the Subject dropdown's <option> list.
 * Uses strict (===) comparison because numeric IDs were cast to int above.
 */
function updateSubjects() {
    const syl = parseInt(adminSyllabus.value) || 0;
    const br  = parseInt(adminBranch.value)   || 0;
    const sem = adminSemester.value; // string e.g. "3"

    // Reset the dropdown before rebuilding
    adminSubject.innerHTML = '<option value="">-- Select Subject --</option>';

    // Nothing to show if any filter is unset
    if (syl === 0 || br === 0 || sem === '') return;

    // Filter the subjects array to only those matching all three criteria
    const filtered = ALL_SUBJECTS.filter(function (s) {
        return s.syllabus_id === syl && s.branch_id === br && s.semester === sem;
    });

    // Add a <option> for each matching subject
    filtered.forEach(function (s) {
        const opt = document.createElement('option');
        opt.value       = s.subject_id;
        opt.textContent = s.subject_name;
        if (s.subject_id === savedSubject) opt.selected = true; // restore sticky value
        adminSubject.appendChild(opt);
    });

    // If no subjects match the combination, show a disabled placeholder
    if (filtered.length === 0) {
        const opt      = document.createElement('option');
        opt.value      = '';
        opt.textContent = 'No subjects found for this combination';
        opt.disabled   = true;
        adminSubject.appendChild(opt);
    }
}

// Re-run updateSubjects whenever any of the three filter dropdowns changes
[adminSyllabus, adminBranch, adminSemester].forEach(function (el) {
    el.addEventListener('change', updateSubjects);
});

// On page load after a validation error: restore subjects if the form
// had values pre-filled (PHP passes them via $form['syllabus_id'] etc.)
if (<?= $form['syllabus_id'] ?> > 0 &&
    <?= $form['branch_id'] ?>   > 0 &&
    <?= json_encode($form['semester']) ?> !== '') {
    updateSubjects();
}

// Reset button: type="reset" clears the HTML inputs, but the Subject
// dropdown was built by JS so we must clear it manually with a brief delay.
document.getElementById('resetBtn').addEventListener('click', function () {
    setTimeout(function () {
        adminSubject.innerHTML = '<option value="">-- Select Subject --</option>';
    }, 10);
});
</script>

</body>
</html>
