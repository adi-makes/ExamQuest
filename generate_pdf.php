<?php
// Detect DOMPDF path: Composer (Option A) or manual (Option B)
$autoload_composer = __DIR__ . '/vendor/autoload.php';
$autoload_manual   = __DIR__ . '/vendor/dompdf/autoload.inc.php';

if (file_exists($autoload_composer)) {
    require_once $autoload_composer;
} elseif (file_exists($autoload_manual)) {
    require_once $autoload_manual;
} else {
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>PDF Error – ExamQuest</title>
        <style>
            body { font-family:system-ui,sans-serif; display:flex; align-items:center;
                   justify-content:center; min-height:100vh; background:#faf8ff; }
            .box { background:#fff; border:1px solid #fecaca; border-radius:10px;
                   padding:40px; max-width:520px; }
            h2 { color:#dc2626; margin-bottom:12px; }
            p  { color:#374151; line-height:1.6; margin-bottom:10px; }
            code { background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:13px; }
            ol   { padding-left:20px; color:#374151; line-height:2; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>DOMPDF Not Installed</h2>
            <p>The PDF library is missing. Install it using one of these methods:</p>
            <ol>
                <li><strong>Option A – Composer (recommended):</strong><br>
                    Open terminal in your project folder and run:<br>
                    <code>composer require dompdf/dompdf</code></li>
                <li><strong>Option B – Manual:</strong><br>
                    Download from <a href="https://github.com/dompdf/dompdf/releases">github.com/dompdf/dompdf/releases</a>,
                    extract to <code>vendor/dompdf/</code></li>
            </ol>
            <p><a href="index.php">← Back to ExamQuest</a></p>
        </div>
    </body>
    </html>
    ');
}

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/db.php';

// Validate subject_id
$subject_id = intval($_GET['subject_id'] ?? 0);
if ($subject_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch subject details
$stmt = $conn->prepare('
    SELECT s.subject_name, s.semester, s.subject_type,
           sy.regulation_year, b.branch_name
    FROM subject s
    JOIN syllabus sy ON sy.syllabus_id = s.syllabus_id
    JOIN branch   b  ON b.branch_id   = s.branch_id
    WHERE s.subject_id = ?
');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$subject) {
    header('Location: index.php');
    exit;
}

// Fetch ALL questions for this subject (no pagination)
$stmt = $conn->prepare('
    SELECT question_text, marks, frequency
    FROM questions
    WHERE subject_id = ?
    ORDER BY frequency DESC, marks DESC
');
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = [];
while ($row = $questions_result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

// Build PDF HTML
function buildPdfHtml(array $subject, array $questions): string {
    $rows = '';
    foreach ($questions as $i => $q) {
        $freq = intval($q['frequency']);
        if ($freq >= 5) {
            $importance  = 'HIGH';
            $bg_color    = '#FEF2F2';
            $badge_color = '#DC2626';
        } elseif ($freq >= 3) {
            $importance  = 'MODERATE';
            $bg_color    = '#EFF6FF';
            $badge_color = '#2563EB';
        } else {
            $importance  = 'LOW';
            $bg_color    = '#F0FDF4';
            $badge_color = '#16A34A';
        }

        $row_bg = $i % 2 === 0 ? '#ffffff' : '#F8FAFC';
        $freq_label = $freq === 1 ? '1 Time' : $freq . ' Times';

        $rows .= '
        <tr style="background:' . $row_bg . '">
            <td style="padding:10px 12px;border-bottom:1px solid #E2E8F0;font-size:11px;line-height:1.6;vertical-align:top;">
                ' . htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') . '
            </td>
            <td style="padding:10px 12px;border-bottom:1px solid #E2E8F0;text-align:center;font-size:11px;font-weight:600;vertical-align:middle;white-space:nowrap;">
                ' . intval($q['marks']) . '
            </td>
            <td style="padding:10px 12px;border-bottom:1px solid #E2E8F0;text-align:center;font-size:11px;vertical-align:middle;white-space:nowrap;">
                ' . htmlspecialchars($freq_label, ENT_QUOTES, 'UTF-8') . '
            </td>
            <td style="padding:10px 12px;border-bottom:1px solid #E2E8F0;text-align:center;vertical-align:middle;">
                <span style="background:' . $bg_color . ';color:' . $badge_color . ';
                             padding:3px 8px;border-radius:999px;font-size:9px;font-weight:700;
                             letter-spacing:0.04em;white-space:nowrap;">
                    ' . $importance . '
                </span>
            </td>
        </tr>';
    }

    $count = count($questions);
    $date  = date('d F Y');

    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: Helvetica, Arial, sans-serif;
        color: #191b23;
        padding: 28px 32px;
        font-size: 11px;
    }
    .header {
        border-bottom: 2px solid #2563EB;
        padding-bottom: 14px;
        margin-bottom: 20px;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }
    .brand {
        font-size: 16px;
        font-weight: 700;
        color: #2563EB;
    }
    .generated {
        font-size: 10px;
        color: #94A3B8;
        text-align: right;
    }
    .title {
        font-size: 18px;
        font-weight: 700;
        color: #191b23;
        margin-bottom: 6px;
    }
    .meta {
        font-size: 10px;
        color: #64748B;
        line-height: 1.8;
    }
    .meta span {
        margin-right: 16px;
    }
    .meta strong {
        color: #374151;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    thead th {
        background: #2563EB;
        color: #ffffff;
        padding: 10px 12px;
        font-size: 10px;
        font-weight: 600;
        text-align: left;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .footer {
        margin-top: 20px;
        padding-top: 12px;
        border-top: 1px solid #E2E8F0;
        font-size: 9px;
        color: #94A3B8;
        text-align: center;
    }
    .count-badge {
        display: inline-block;
        background: #EEF2FF;
        color: #4338CA;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
        vertical-align: middle;
    }
</style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="brand">ExamQuest</div>
            <div class="generated">Generated: ' . $date . '</div>
        </div>
        <div class="title">
            Frequently Repeated Exam Questions
            <span class="count-badge">' . $count . ' Questions</span>
        </div>
        <div class="meta">
            <span><strong>Subject:</strong> ' . htmlspecialchars($subject['subject_name'], ENT_QUOTES, 'UTF-8') . '</span>
            <span><strong>Syllabus:</strong> ' . htmlspecialchars($subject['regulation_year'], ENT_QUOTES, 'UTF-8') . '</span>
            <span><strong>Branch:</strong> ' . htmlspecialchars($subject['branch_name'], ENT_QUOTES, 'UTF-8') . '</span>
            <span><strong>Semester:</strong> ' . htmlspecialchars($subject['semester'], ENT_QUOTES, 'UTF-8') . '</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:54%;">Question</th>
                <th style="width:10%;text-align:center;">Marks</th>
                <th style="width:16%;text-align:center;">Repeated</th>
                <th style="width:20%;text-align:center;">Importance</th>
            </tr>
        </thead>
        <tbody>
            ' . ($rows ?: '<tr><td colspan="4" style="padding:20px;text-align:center;color:#94A3B8;">No questions found.</td></tr>') . '
        </tbody>
    </table>

    <div class="footer">
        ExamQuest – Smart Question Bank Explorer &nbsp;|&nbsp;
        Developed as part of DBMS Mini Project &nbsp;|&nbsp;
        Questions sorted by exam frequency
    </div>
</body>
</html>';
}

// Configure and render DOMPDF
$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml(buildPdfHtml($subject, $questions));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Safe filename
$safe_name = preg_replace('/[^a-z0-9_]/i', '_', $subject['subject_name']);
$safe_name = preg_replace('/_+/', '_', $safe_name);
$filename  = 'examquest_' . $safe_name . '_' . date('Ymd') . '.pdf';

$dompdf->stream($filename, ['Attachment' => true]);
