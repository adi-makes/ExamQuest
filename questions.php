<?php
require_once 'db.php';

define('PER_PAGE', 6);

// Validate incoming params
$subject_id  = intval($_GET['subject_id']  ?? 0);
$syllabus_id = intval($_GET['syllabus_id'] ?? 0);
$branch_id   = intval($_GET['branch_id']   ?? 0);
$semester    = trim($_GET['semester']       ?? '');
$page        = max(1, intval($_GET['page'] ?? 1));

if ($subject_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch subject details + breadcrumb labels
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

if (!$subject_info) {
    header('Location: index.php');
    exit;
}

// Fill in missing URL params from DB record if not passed
if ($syllabus_id <= 0) $syllabus_id = intval($subject_info['syllabus_id']);
if ($branch_id   <= 0) $branch_id   = intval($subject_info['branch_id']);
if ($semester === '')  $semester    = $subject_info['semester'];

// Count total questions
$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM questions WHERE subject_id = ?');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$total_questions = intval($stmt->get_result()->fetch_assoc()['total']);
$stmt->close();

$total_pages = $total_questions > 0 ? (int)ceil($total_questions / PER_PAGE) : 1;
$page = min($page, $total_pages);
$offset = ($page - 1) * PER_PAGE;

// Fetch paginated questions
$stmt = $conn->prepare('
    SELECT question_id, question_text, marks, frequency
    FROM questions
    WHERE subject_id = ?
    ORDER BY frequency DESC, marks DESC
    LIMIT ? OFFSET ?
');
$stmt->bind_param('iii', $subject_id, PER_PAGE, $offset);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = [];
while ($row = $questions_result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

// Badge logic
function getImportance(int $freq): array {
    if ($freq >= 5) return ['label' => 'HIGH PRIORITY',     'class' => 'badge--red'];
    if ($freq >= 3) return ['label' => 'MODERATE PRIORITY', 'class' => 'badge--blue'];
    return              ['label' => 'LOW PRIORITY',         'class' => 'badge--green'];
}

function getRepeatedLabel(int $freq): string {
    return $freq === 1 ? '1 Time' : $freq . ' Times';
}

// Bottom stats
$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM questions WHERE subject_id = ? AND marks >= 10');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$long_form_count = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

$long_form_pct = $total_questions > 0 ? round(($long_form_count / $total_questions) * 100) : 0;

$stmt = $conn->prepare('SELECT MAX(frequency) AS max_freq FROM questions WHERE subject_id = ?');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$max_freq = intval($stmt->get_result()->fetch_assoc()['max_freq']);
$stmt->close();

$stmt = $conn->prepare('SELECT question_text FROM questions WHERE subject_id = ? ORDER BY frequency DESC LIMIT 1');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$top_row = $stmt->get_result()->fetch_assoc();
$top_topic = $top_row ? (strlen($top_row['question_text']) > 60 ? substr($top_row['question_text'], 0, 60) . '…' : $top_row['question_text']) : 'N/A';
$stmt->close();

// Build URL for pagination links
function pageUrl(int $p): string {
    $params = $_GET;
    $params['page'] = $p;
    return '?' . http_build_query($params);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject_info['subject_name'], ENT_QUOTES, 'UTF-8') ?> – ExamQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation -->
<nav class="nav">
    <div class="nav__inner">
        <a href="index.php" class="nav__brand">ExamQuest</a>
        <div class="nav__links">
            <a href="index.php">Home</a>
            <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>" class="active">Subjects</a>
            <a href="admin.php">Admin</a>
            <a href="about.php">About</a>
        </div>
        <div class="nav__actions">
            <button class="nav__icon-btn" title="Search" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <button class="nav__icon-btn" title="Account" aria-label="Account">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </button>
        </div>
    </div>
</nav>

<main>
    <div class="container questions-layout">

        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb__sep">›</span>
            <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>">Subjects</a>
            <span class="breadcrumb__sep">›</span>
            <span class="breadcrumb__current"><?= htmlspecialchars(strtoupper($subject_info['subject_name']), ENT_QUOTES, 'UTF-8') ?></span>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Frequently Repeated Exam Questions</h1>
                <p>Questions ranked based on previous exam frequency.</p>
            </div>
            <a href="generate_pdf.php?subject_id=<?= $subject_id ?>" class="btn btn--pdf">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Question Set (PDF)
            </a>
        </div>

        <!-- Active Filters -->
        <div class="card active-filters">
            <span class="active-filters__label">Active Filters:</span>
            <span class="chip">Subject: <?= htmlspecialchars($subject_info['subject_name'], ENT_QUOTES, 'UTF-8') ?> <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>">×</a></span>
            <span class="chip">Status: Most Repeated <a href="<?= pageUrl(1) ?>">×</a></span>
            <a href="subject.php?syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>" class="link--clear">Clear All</a>
        </div>

        <!-- Question Table -->
        <?php if (empty($questions)): ?>
        <div class="card" style="text-align:center;padding:48px;color:var(--color-secondary-text);">
            <p>No questions available for this subject yet.</p>
            <a href="admin.php" class="btn btn--primary" style="margin-top:16px;">Add Questions</a>
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
                    <?php foreach ($questions as $i => $q):
                          $imp = getImportance(intval($q['frequency']));
                    ?>
                    <tr>
                        <td>
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
                            <span class="badge <?= $imp['class'] ?>"><?= $imp['label'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <span class="pagination__info">
                    Showing <?= $offset + 1 ?> to <?= min($offset + PER_PAGE, $total_questions) ?> of <?= $total_questions ?> questions
                </span>
                <div class="pagination__pages">
                    <?php if ($page > 1): ?>
                    <a href="<?= pageUrl($page - 1) ?>" class="page-btn" aria-label="Previous page">‹</a>
                    <?php endif; ?>

                    <?php
                    // Show first, ellipsis, window around current, ellipsis, last
                    $window = 2;
                    $shown = [];
                    for ($p = 1; $p <= $total_pages; $p++) {
                        if ($p === 1 || $p === $total_pages || abs($p - $page) <= $window) {
                            $shown[] = $p;
                        }
                    }
                    $prev = null;
                    foreach ($shown as $p):
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

                    <?php if ($page < $total_pages): ?>
                    <a href="<?= pageUrl($page + 1) ?>" class="page-btn" aria-label="Next page">›</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bottom Stats Row -->
        <div class="stats-row">
            <div class="card stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    </div>
                    <span class="stat-card__eyebrow">Most Likely</span>
                </div>
                <div style="font-size:15px;font-weight:600;color:var(--color-on-surface);line-height:1.4;margin-bottom:4px;">
                    <?= htmlspecialchars(strlen($top_topic) > 80 ? substr($top_topic, 0, 80) . '…' : $top_topic, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="stat-card__note">Identified as the highest frequency question this semester.</div>
            </div>

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

<!-- Footer -->
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
