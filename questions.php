<?php
/**
 * questions.php — Question Listing Page
 *
 * Displays all exam questions for a chosen subject, paginated and sorted
 * by frequency (most-repeated first), then by marks (highest first).
 *
 * Expected GET params:
 *   subject_id   (int)  — which subject to show
 *   syllabus_id  (int)  — used only to rebuild breadcrumb/back links
 *   branch_id    (int)  — used only to rebuild breadcrumb/back links
 *   semester     (str)  — used only to rebuild breadcrumb/back links
 *   page         (int)  — current page number (default 1)
 *
 * If subject_id is missing or invalid, the user is sent back to index.php.
 */

require_once 'db.php';

// ── Constants ─────────────────────────────────────────────────────────────────
define('PER_PAGE', 6); // Number of questions shown per page

// ── Read and sanitise GET parameters ─────────────────────────────────────────
$subject_id  = intval($_GET['subject_id']  ?? 0);
$syllabus_id = intval($_GET['syllabus_id'] ?? 0);
$branch_id   = intval($_GET['branch_id']   ?? 0);
$semester    = trim($_GET['semester']       ?? '');
$page        = max(1, intval($_GET['page'] ?? 1)); // page must be at least 1

if ($subject_id <= 0) {
    header('Location: index.php');
    exit;
}

// ── Fetch subject details ─────────────────────────────────────────────────────
// We JOIN syllabus and branch here so we get all display labels in one query
// instead of three separate queries. This is a three-table INNER JOIN:
//   subject → syllabus  (many-to-one: each subject belongs to one syllabus)
//   subject → branch    (many-to-one: each subject belongs to one branch)

$stmt = $conn->prepare('
    SELECT s.subject_name, s.semester, s.subject_type,
           sy.regulation_year, b.branch_name,
           s.syllabus_id, s.branch_id
    FROM subject s
    JOIN syllabus sy ON sy.syllabus_id = s.syllabus_id
    JOIN branch   b  ON b.branch_id   = s.branch_id
    WHERE s.subject_id = ?
');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$subject_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If the subject_id doesn't exist in the DB, redirect gracefully
if (!$subject_info) {
    header('Location: index.php');
    exit;
}

// ── Fill missing URL params from the DB record ────────────────────────────────
// The user may arrive from a direct link that omits syllabus_id/branch_id.
// Fall back to the values stored on the subject row so breadcrumb links still work.

if ($syllabus_id <= 0) $syllabus_id = intval($subject_info['syllabus_id']);
if ($branch_id   <= 0) $branch_id   = intval($subject_info['branch_id']);
if ($semester === '')  $semester    = $subject_info['semester'];

// ── Pagination calculations ───────────────────────────────────────────────────
// COUNT(*) is fast in InnoDB when the WHERE clause uses an indexed column
// (subject_id is a foreign key, so it is automatically indexed).

$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM questions WHERE subject_id = ?');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$total_questions = intval($stmt->get_result()->fetch_assoc()['total']);
$stmt->close();

// Avoid division-by-zero: if there are no questions, treat total_pages as 1
$total_pages = $total_questions > 0 ? (int)ceil($total_questions / PER_PAGE) : 1;

// Clamp the page number so an out-of-range ?page= param doesn't break the OFFSET
$page   = min($page, $total_pages);
$offset = ($page - 1) * PER_PAGE; // rows to skip before the current page

// ── Fetch paginated questions ─────────────────────────────────────────────────
// ORDER BY frequency DESC → most-repeated questions surface first
// ORDER BY marks DESC     → for equal frequency, higher-marks questions come first
// LIMIT ? OFFSET ?        → returns only the current page's slice of results

$stmt = $conn->prepare('
    SELECT question_id, question_text, marks, frequency
    FROM questions
    WHERE subject_id = ?
    ORDER BY frequency DESC, marks DESC
    LIMIT ? OFFSET ?
');
$per_page = PER_PAGE; // must be a variable for bind_param
$stmt->bind_param('iii', $subject_id, $per_page, $offset);
$stmt->execute();
$questions_result = $stmt->get_result();

$questions = [];
while ($row = $questions_result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

// ── Helper: importance badge ──────────────────────────────────────────────────
// Maps a question's frequency count to a priority label and CSS modifier class.
// Thresholds: >= 5 = HIGH, >= 3 = MODERATE, < 3 = LOW.

function getImportance(int $freq): array {
    if ($freq >= 5) return ['label' => 'HIGH PRIORITY',     'class' => 'badge--red'];
    if ($freq >= 3) return ['label' => 'MODERATE PRIORITY', 'class' => 'badge--blue'];
    return              ['label' => 'LOW PRIORITY',         'class' => 'badge--green'];
}

// ── Helper: frequency label ───────────────────────────────────────────────────
// Converts a raw count into a readable string ("1 Time" / "N Times").

function getRepeatedLabel(int $freq): string {
    return $freq === 1 ? '1 Time' : $freq . ' Times';
}

// ── Bottom stats: long-form question percentage ───────────────────────────────
// "Long-form" means marks >= 10 (typical for 10-mark essay / derivation questions).
// We calculate the % to show in the Score Density stat card.

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions WHERE subject_id = ? AND marks >= 10');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$long_form_count = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

$long_form_pct = $total_questions > 0
    ? round(($long_form_count / $total_questions) * 100)
    : 0;

// ── Bottom stats: maximum frequency ──────────────────────────────────────────
// Used in the "Data Source / Previous Papers" stat card.
// MAX(frequency) tells us how many papers the top question appeared in.

$stmt = $conn->prepare('SELECT MAX(frequency) AS max_freq FROM questions WHERE subject_id = ?');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$max_freq = intval($stmt->get_result()->fetch_assoc()['max_freq']);
$stmt->close();

// ── Bottom stats: top-frequency question preview ──────────────────────────────
// Fetches the question text with the highest frequency for the "Most Likely" card.
// Truncated to 80 characters to keep the card tidy.

$stmt = $conn->prepare('
    SELECT question_text
    FROM questions
    WHERE subject_id = ?
    ORDER BY frequency DESC
    LIMIT 1
');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$top_row   = $stmt->get_result()->fetch_assoc();
$top_topic = $top_row
    ? (strlen($top_row['question_text']) > 80
        ? substr($top_row['question_text'], 0, 80) . '…'
        : $top_row['question_text'])
    : 'N/A';
$stmt->close();

// ── Helper: build a URL for a specific page number ───────────────────────────
// Carries all current GET params forward (subject_id, syllabus_id, etc.)
// and only replaces/adds the `page` key. This keeps the URL consistent
// across pagination clicks without losing filter context.

function pageUrl(int $p): string {
    $params         = $_GET;
    $params['page'] = $p;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic page title shows the subject name -->
    <title><?= htmlspecialchars($subject_info['subject_name'], ENT_QUOTES, 'UTF-8') ?> – ExamQuest</title>
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
    <div class="container questions-layout">

        <!-- ── Breadcrumb trail ───────────────────────────────────────────── -->
        <!-- Shows: Home › Subjects › SUBJECT NAME
             The "Subjects" link rebuilds subject.php with the current filters. -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb__sep">›</span>
            <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>">Subjects</a>
            <span class="breadcrumb__sep">›</span>
            <span class="breadcrumb__current"><?= htmlspecialchars(strtoupper($subject_info['subject_name']), ENT_QUOTES, 'UTF-8') ?></span>
        </nav>

        <!-- ── Page header ────────────────────────────────────────────────── -->
        <div class="page-header">
            <div>
                <h1>Frequently Repeated Exam Questions</h1>
                <p>Questions ranked based on previous exam frequency.</p>
            </div>
        </div>

        <!-- ── Active filter chips ───────────────────────────────────────── -->
        <!-- Shows which subject is active. The × links let users deselect. -->
        <div class="card active-filters">
            <span class="active-filters__label">Active Filters:</span>
            <span class="chip">
                Subject: <?= htmlspecialchars($subject_info['subject_name'], ENT_QUOTES, 'UTF-8') ?>
                <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>">×</a>
            </span>
            <span class="chip">
                Status: Most Repeated
                <a href="<?= pageUrl(1) ?>">×</a>
            </span>
            <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>" class="link--clear">Clear All</a>
        </div>

        <!-- ── Question table ─────────────────────────────────────────────── -->
        <?php if (empty($questions)): ?>
        <!-- Empty state — no questions in the DB for this subject yet -->
        <div class="card" style="text-align:center;padding:48px;color:var(--color-secondary-text);">
            <p>No questions available for this subject yet.</p>
        </div>
        <?php else: ?>
        <div class="card question-table">
            <table>
                <thead>
                    <tr>
                        <th>Question</th>
                        <th style="width:80px;">Marks</th>
                        <th style="width:120px;">Repeated</th>
                        <th style="width:150px;">Importance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q):
                        // Compute the badge label and CSS class for each row
                        $imp = getImportance(intval($q['frequency']));
                    ?>
                    <tr>
                        <td>
                            <!-- htmlspecialchars() prevents XSS by escaping < > " & -->
                            <p class="question-text"><?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="question-meta">
                                <?= htmlspecialchars($subject_info['subject_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="marks-cell"><?= intval($q['marks']) ?></td>
                        <td class="freq-cell">
                            <span class="freq-badge"><?= getRepeatedLabel(intval($q['frequency'])) ?></span>
                        </td>
                        <td class="importance-cell">
                            <!-- badge--red / badge--blue / badge--green set via $imp['class'] -->
                            <span class="badge <?= $imp['class'] ?>"><?= $imp['label'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ── Pagination controls ────────────────────────────────────── -->
            <div class="pagination">
                <!-- "Showing X to Y of Z questions" summary -->
                <span class="pagination__info">
                    Showing <?= $offset + 1 ?> to <?= min($offset + PER_PAGE, $total_questions) ?> of <?= $total_questions ?> questions
                </span>

                <div class="pagination__pages">

                    <!-- Previous page arrow (hidden on first page) -->
                    <?php if ($page > 1): ?>
                    <a href="<?= pageUrl($page - 1) ?>" class="page-btn" aria-label="Previous page">‹</a>
                    <?php endif; ?>

                    <?php
                    // Smart page-number list:
                    // Always show page 1 and the last page.
                    // Show pages within $window steps of the current page.
                    // Everything else becomes an ellipsis "…".
                    $window = 2;
                    $shown  = [];
                    for ($p = 1; $p <= $total_pages; $p++) {
                        if ($p === 1 || $p === $total_pages || abs($p - $page) <= $window) {
                            $shown[] = $p;
                        }
                    }

                    $prev = null;
                    foreach ($shown as $p):
                        // Insert an ellipsis when there is a gap between page numbers
                        if ($prev !== null && $p - $prev > 1): ?>
                        <span class="page-btn page-btn--ellipsis">…</span>
                    <?php endif; ?>
                    <a href="<?= pageUrl($p) ?>"
                       class="page-btn <?= $p === $page ? 'page-btn--active' : '' ?>"
                       aria-label="Page <?= $p ?>"
                       <?= $p === $page ? 'aria-current="page"' : '' ?>>
                        <?= $p ?>
                    </a>
                    <?php $prev = $p;
                    endforeach; ?>

                    <!-- Next page arrow (hidden on last page) -->
                    <?php if ($page < $total_pages): ?>
                    <a href="<?= pageUrl($page + 1) ?>" class="page-btn" aria-label="Next page">›</a>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Stats row ──────────────────────────────────────────────────── -->
        <!-- Three summary cards below the question table. -->
        <div class="stats-row">

            <!-- Card 1: Top question preview -->
            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">Most Likely</span>
                </div>
                <!-- $top_topic is already truncated to 80 chars in PHP above -->
                <div style="font-size:15px;font-weight:600;color:var(--color-on-surface);line-height:1.4;margin-bottom:4px;">
                    <?= htmlspecialchars($top_topic, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="stat-card__note">Identified as the highest frequency question this semester.</div>
            </div>

            <!-- Card 2: Long-form question percentage -->
            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">Score Density</span>
                </div>
                <div class="stat-card__value"><?= $long_form_pct ?>%</div>
                <div class="stat-card__label">Long-form Questions</div>
                <div class="stat-card__note">Focus on theory and multi-step problems.</div>
            </div>

            <!-- Card 3: Maximum frequency (= number of papers analysed) -->
            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">Data Source</span>
                </div>
                <div class="stat-card__value"><?= $max_freq ?></div>
                <div class="stat-card__label">Previous Papers</div>
                <div class="stat-card__note">Aggregated from University exams.</div>
            </div>

        </div>

    </div>
</main>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="footer">
    <div class="footer__brand">ExamQuest</div>
    <div class="footer__tagline">Developed as part of DBMS Mini Project</div>
    <div class="footer__links">
        <a href="#">Share</a>
        <a href="#">Help</a>
    </div>
</footer>

</body>
</html>
