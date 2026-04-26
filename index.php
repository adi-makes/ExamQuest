<?php
/**
 * index.php — Landing Page / Filter Form
 *
 * This is the entry point of ExamQuest. It renders a three-field
 * filter form (Syllabus, Branch, Semester) and, on submission,
 * redirects the user to subject.php with those values as GET params.
 *
 * Flow:
 *   GET  request → render the form with dropdown options
 *   POST request → validate input → redirect to subject.php (PRG pattern)
 *
 * The Post-Redirect-Get (PRG) pattern is used so that pressing F5
 * after submitting does not re-POST the form.
 */

require_once 'db.php';

// ── Populate dropdown options ─────────────────────────────────────────────────
// These two simple SELECT queries feed the Syllabus and Branch dropdowns.
// mysqli_query() is used here (not a prepared statement) because there are
// no user-supplied values — the queries are fully static.

$syllabi  = mysqli_query($conn, 'SELECT syllabus_id, regulation_year FROM syllabus ORDER BY syllabus_id ASC');
$branches = mysqli_query($conn, 'SELECT branch_id, branch_name FROM branch ORDER BY branch_name ASC');

// Semester options are derived from subjects that actually exist in the DB,
// not hardcoded, so the list stays accurate as new subjects are added.
$semesters_result = mysqli_query($conn, 'SELECT DISTINCT semester FROM subject ORDER BY CAST(semester AS UNSIGNED) ASC');
$semesters = [];
while ($row = mysqli_fetch_assoc($semesters_result)) {
    $semesters[] = $row['semester'];
}

// ── Handle form submission (POST → validate → redirect) ───────────────────────
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cast to int / trim to sanitise the incoming values.
    // intval() returns 0 for empty or non-numeric input.
    $syllabus_id = intval($_POST['syllabus_id'] ?? 0);
    $branch_id   = intval($_POST['branch_id']   ?? 0);
    $semester    = trim($_POST['semester']       ?? '');

    if ($syllabus_id > 0 && $branch_id > 0 && $semester !== '') {
        // All three fields present — redirect to the subject listing page.
        // http_build_query() safely URL-encodes the parameters.
        header('Location: subject.php?syllabus_id=' . $syllabus_id . '&branch_id=' . $branch_id . '&semester=' . urlencode($semester));
        exit;
    } else {
        // Show an inline error; the form re-renders with the user's choices.
        $error = 'Please select all three fields before continuing.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExamQuest – Smart Question Bank Explorer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ── Navigation bar ─────────────────────────────────────────────────────── -->
<!-- Sticky top nav with brand name and a single Home link. -->
<nav class="nav">
    <div class="nav__inner">
        <a href="index.php" class="nav__brand">ExamQuest</a>
        <div class="nav__links">
            <a href="index.php" class="active">Home</a>
        </div>
    </div>
</nav>

<main>

    <!-- ── Hero section ───────────────────────────────────────────────────── -->
    <!-- Large centered header followed by the filter card. -->
    <section class="hero">
        <span class="hero__eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            Academic Excellence Simplified
        </span>
        <h1 class="hero__title">Find Important Repeated Exam Questions Instantly</h1>
        <p class="hero__sub">Select your syllabus, branch, and year to explore frequently asked university exam questions.</p>

        <!-- ── Filter card (the actual form) ────────────────────────────── -->
        <div class="card filter-card">

            <!-- Validation error — shown only after a failed POST attempt -->
            <?php if ($error): ?>
            <div class="alert alert--error">
                <span class="alert__icon">!</span>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>

            <!-- POST to self; PHP at the top handles the redirect -->
            <form method="POST" action="index.php">

                <!-- Syllabus dropdown — regulation year (e.g. "CUSAT 2023 Scheme") -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" for="syllabus_id">Select Syllabus</label>
                    <select name="syllabus_id" id="syllabus_id" class="input input--select" required>
                        <option value="">Choose Regulation</option>
                        <?php
                        // Reset result pointer (it may have been read already above)
                        mysqli_data_seek($syllabi, 0);
                        while ($s = mysqli_fetch_assoc($syllabi)):
                        ?>
                        <option value="<?= $s['syllabus_id'] ?>"
                            <?= (isset($_POST['syllabus_id']) && $_POST['syllabus_id'] == $s['syllabus_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['regulation_year'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Branch dropdown — engineering department -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" for="branch_id">Select Branch</label>
                    <select name="branch_id" id="branch_id" class="input input--select" required>
                        <option value="">Choose Branch</option>
                        <?php
                        mysqli_data_seek($branches, 0);
                        while ($b = mysqli_fetch_assoc($branches)):
                        ?>
                        <option value="<?= $b['branch_id'] ?>"
                            <?= (isset($_POST['branch_id']) && $_POST['branch_id'] == $b['branch_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['branch_name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Semester dropdown — pulled live from the DB so only real semesters appear -->
                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" for="semester">Select Semester</label>
                    <select name="semester" id="semester" class="input input--select" required>
                        <option value="">Choose Semester</option>
                        <?php foreach ($semesters as $s): ?>
                        <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"
                            <?= (isset($_POST['semester']) && $_POST['semester'] === $s) ? 'selected' : '' ?>>
                            Semester <?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit triggers the POST handler at the top of this file -->
                <button type="submit" class="btn btn--primary btn--full" style="margin-bottom:12px;">
                    Explore Subjects &nbsp;→
                </button>
            </form>

            <p class="filter-card__note">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                No login required. Access curated exam questions instantly.
            </p>
        </div>
    </section>

    <!-- ── Feature cards ──────────────────────────────────────────────────── -->
    <!-- Static marketing section — no dynamic content. -->
    <section class="features">
        <div class="features__grid">

            <!-- Large hero card (spans two rows in the CSS grid) -->
            <div class="feature-card feature-card--hero">
                <div class="feature-card__content">
                    <h3>Curated Question Bank</h3>
                    <p>Our database focuses on high-yield questions that appear repeatedly across multiple university examination cycles.</p>
                </div>
            </div>

            <div class="feature-card feature-card--white">
                <div class="feature-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </div>
                <h3>Updated for 2024</h3>
                <p>Stay ahead with the latest syllabus regulations and recent question patterns sourced directly from university archives.</p>
            </div>

            <div class="feature-card feature-card--white">
                <div class="feature-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <h3>Zero Wait Time</h3>
                <p>No ads, no registration, no distractions. Just find your subject and start preparing for your exams immediately.</p>
            </div>

            <div class="feature-card feature-card--white">
                <div class="feature-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <h3>Smart Organization</h3>
                <p>Questions are categorized by module and weightage, helping you prioritize high-scoring topics during last-minute revisions.</p>
                <div class="feature-tag-row">
                    <span class="chip" style="font-size:11px;padding:2px 8px;">MODULE WISE</span>
                    <span class="chip" style="font-size:11px;padding:2px 8px;">REPEATED</span>
                </div>
            </div>

        </div>
    </section>
</main>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="footer" style="padding-left:24px;padding-right:24px;">
    <div>
        <div class="footer__brand">ExamQuest</div>
        <div class="footer__tagline">ExamQuest – Smart Question Bank Explorer</div>
    </div>
    <div class="footer__tagline">Developed as part of DBMS Mini Project</div>
    <div class="footer__links">
        <a href="#">Help Desk</a>
        <a href="#">Privacy</a>
    </div>
</footer>

</body>
</html>
