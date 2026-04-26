<?php
/**
 * subject.php — Subject Listing Page
 *
 * Displays all subjects that match the selected syllabus, branch,
 * and semester combination passed in via GET parameters.
 *
 * Expected GET params:
 *   syllabus_id (int)  — ID from the syllabus table
 *   branch_id   (int)  — ID from the branch table
 *   semester    (str)  — e.g. "3" or "4"
 *
 * If any param is missing or invalid, the user is sent back to index.php.
 */

require_once 'db.php';

// ── Read and validate GET parameters ─────────────────────────────────────────
// intval() converts strings to integers safely — non-numeric input becomes 0.
// We treat 0 as "not provided" and redirect away.

$syllabus_id = intval($_GET['syllabus_id'] ?? 0);
$branch_id   = intval($_GET['branch_id']   ?? 0);
$semester    = trim($_GET['semester']       ?? '');

if ($syllabus_id <= 0 || $branch_id <= 0 || $semester === '') {
    header('Location: index.php');
    exit;
}

// ── Fetch label text for the active-filter chips ──────────────────────────────
// We display the human-readable names ("CUSAT 2023 Scheme", "CSE") in the UI
// rather than raw IDs, so we look them up separately.

$stmt = $conn->prepare('SELECT regulation_year FROM syllabus WHERE syllabus_id = ?');
$stmt->bind_param('i', $syllabus_id);
$stmt->execute();
$syllabus_label = $stmt->get_result()->fetch_assoc()['regulation_year'] ?? 'Unknown';
$stmt->close();

$stmt = $conn->prepare('SELECT branch_name FROM branch WHERE branch_id = ?');
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$branch_label = $stmt->get_result()->fetch_assoc()['branch_name'] ?? 'Unknown';
$stmt->close();

// ── Fetch subjects with their question count ──────────────────────────────────
// This JOIN query:
//   - Starts from the `subject` table (filtered by syllabus, branch, semester)
//   - LEFT JOINs `questions` so subjects with zero questions still appear
//   - Uses COUNT(q.question_id) to count only matched question rows (not NULLs)
//   - Groups by subject_id so COUNT works per-subject
//   - Orders alphabetically for consistent display

$stmt = $conn->prepare('
    SELECT s.subject_id, s.subject_name, s.semester, s.subject_type,
           COUNT(q.question_id) AS question_count
    FROM subject s
    LEFT JOIN questions q ON q.subject_id = s.subject_id
    WHERE s.syllabus_id = ? AND s.branch_id = ? AND s.semester = ?
    GROUP BY s.subject_id
    ORDER BY s.subject_name ASC
');
$stmt->bind_param('iis', $syllabus_id, $branch_id, $semester);
$stmt->execute();
$subjects_result = $stmt->get_result();

$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

// ── Subject icon mapping ──────────────────────────────────────────────────────
// Returns an SVG string based on keywords in the subject name.
// This gives each card a contextually appropriate icon without storing
// icon data in the database. The final `return` is the generic fallback.

function getSubjectIconSvg(string $name): string {
    $n = strtolower($name); // normalise to lowercase for case-insensitive matching

    if (str_contains($n, 'database') || str_contains($n, 'dbms')) {
        // Cylinder icon — represents a database server
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>';
    }
    if (str_contains($n, 'network')) {
        // Node-and-edge graph — represents networking
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><line x1="12" y1="7" x2="5" y2="17"/><line x1="12" y1="7" x2="19" y2="17"/><line x1="5" y1="19" x2="19" y2="19"/></svg>';
    }
    if (str_contains($n, 'operating') || str_contains($n, 'os')) {
        // Monitor icon — represents an OS / system software subject
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
    }
    if (str_contains($n, 'machine learning') || str_contains($n, 'artificial') || str_contains($n, 'ai')) {
        // Target/arrow icon — represents AI / ML
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 12l4-4"/><circle cx="18" cy="6" r="3"/></svg>';
    }
    if (str_contains($n, 'software')) {
        // Code brackets — represents software engineering
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';
    }
    if (str_contains($n, 'micro') || str_contains($n, 'embedded') || str_contains($n, 'vlsi')) {
        // Chip icon — represents microprocessors / embedded systems
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="7" width="10" height="10" rx="1"/><path d="M9 7V4M15 7V4M9 20v-3M15 20v-3M4 9H7M4 15H7M17 9h3M17 15h3"/></svg>';
    }
    if (str_contains($n, 'formal') || str_contains($n, 'automata') || str_contains($n, 'discrete') || str_contains($n, 'math')) {
        // Diagonal line between two circles — represents formal/abstract topics
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>';
    }
    if (str_contains($n, 'data structure') || str_contains($n, 'algorithm')) {
        // Three horizontal lines — represents ordered/structured lists
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
    }
    if (str_contains($n, 'java') || str_contains($n, 'object') || str_contains($n, 'oop')) {
        // People/group icon — represents OOP (objects as entities)
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
    }
    if (str_contains($n, 'cloud') || str_contains($n, 'security') || str_contains($n, 'crypto')) {
        // Lock icon — represents security topics
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
    }

    // Default — open book icon, used when no keyword matches
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Subject – ExamQuest</title>
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
    <div class="container">

        <!-- ── Breadcrumb trail ───────────────────────────────────────────── -->
        <!-- Shows: Home › Subjects -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb__sep">›</span>
            <span class="breadcrumb__current">Subjects</span>
        </nav>

        <!-- ── Page title ─────────────────────────────────────────────────── -->
        <div class="subjects-header">
            <h1>Choose Your Subject</h1>
            <p>Select a subject to view frequently repeated examination questions.</p>
        </div>

        <!-- ── Active filters bar ────────────────────────────────────────── -->
        <!-- Shows the current Syllabus / Branch / Semester selection as chips.
             Each chip has an × link that sends the user back to index.php
             to change that particular filter. -->
        <div class="card filter-bar" style="padding:16px 20px;">
            <div class="filter-bar__left">
                <span class="filter-bar__label">Selected Filters:</span>
                <span class="chip">
                    Syllabus: <?= htmlspecialchars($syllabus_label, ENT_QUOTES, 'UTF-8') ?>
                    <a href="index.php" title="Change syllabus">×</a>
                </span>
                <span class="chip">
                    Branch: <?= htmlspecialchars($branch_label, ENT_QUOTES, 'UTF-8') ?>
                    <a href="index.php" title="Change branch">×</a>
                </span>
                <span class="chip">
                    Semester: <?= htmlspecialchars($semester, ENT_QUOTES, 'UTF-8') ?>
                    <a href="index.php" title="Change semester">×</a>
                </span>
            </div>

            <!-- Client-side search input — filtered by the JS at the bottom -->
            <div class="filter-bar__right">
                <input type="text"
                       id="subjectSearch"
                       class="input input--search"
                       placeholder="Search subjects..."
                       style="width:220px;"
                       aria-label="Search subjects">
            </div>
        </div>

        <!-- ── Subject grid ───────────────────────────────────────────────── -->
        <?php if (empty($subjects)): ?>
        <!-- No subjects found for this filter combination -->
        <div style="text-align:center;padding:60px 24px;color:var(--color-secondary-text);">
            <p style="font-size:16px;margin-bottom:8px;">No subjects available for the selected filters.</p>
            <p style="font-size:14px;">Try going back and selecting a different combination.</p>
            <a href="index.php" class="btn btn--primary" style="margin-top:16px;">← Back to Home</a>
        </div>
        <?php else: ?>
        <!-- data-name holds a lowercase copy of the subject name for JS search filtering -->
        <div class="subject-grid" id="subjectGrid">
            <?php foreach ($subjects as $subj): ?>
            <div class="card subject-card" data-name="<?= htmlspecialchars(strtolower($subj['subject_name']), ENT_QUOTES, 'UTF-8') ?>">

                <!-- Contextual icon chosen by getSubjectIconSvg() -->
                <div class="subject-card__icon">
                    <?= getSubjectIconSvg($subj['subject_name']) ?>
                </div>

                <h3 class="subject-card__name"><?= htmlspecialchars($subj['subject_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="subject-card__meta">
                    <?= htmlspecialchars($subj['subject_type'], ENT_QUOTES, 'UTF-8') ?>
                    &bull; Semester <?= htmlspecialchars($subj['semester'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="subject-card__divider"></div>

                <!-- Footer row: question count + link to questions.php -->
                <div class="subject-card__footer">
                    <span class="subject-card__count"><?= intval($subj['question_count']) ?> Questions</span>
                    <!-- Pass all filter params forward so questions.php can build the breadcrumb -->
                    <a href="questions.php?subject_id=<?= $subj['subject_id'] ?>&syllabus_id=<?= $syllabus_id ?>&branch_id=<?= $branch_id ?>&semester=<?= urlencode($semester) ?>"
                       class="btn btn--primary btn--sm">
                        View Questions &nbsp;→
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── "Can't find subject?" prompt ──────────────────────────────── -->
        <!-- Always shown; encourages users to go back and try different filters -->
        <div class="card card--dashed empty-state">
            <div class="empty-state__icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </div>
            <h3>Can't find your subject?</h3>
            <p>Try adjusting your filters or request a new subject to be indexed.</p>
            <a href="index.php" class="btn btn--outline">Request Subject</a>
        </div>

    </div>
</main>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="footer">
    <div>
        <div class="footer__brand">ExamQuest</div>
        <div class="footer__tagline">ExamQuest – Smart Question Bank Explorer</div>
    </div>
    <div class="footer__tagline">Developed as part of DBMS Mini Project</div>
</footer>

<!-- ── Client-side subject search ────────────────────────────────────────── -->
<script>
// Listen for keystrokes in the search box.
// For each card, compare the query string against the lowercased subject name
// stored in data-name. Show or hide the card accordingly.
// This runs entirely in the browser — no extra server requests needed.

const searchInput = document.getElementById('subjectSearch');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.subject-card').forEach(function (card) {
            const name = card.dataset.name || '';
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });
}
</script>

</body>
</html>
