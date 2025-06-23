<?php
$files = glob("inbox/*.txt");
rsort($files);

// Delete handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileToDelete = $_POST['delete_file'];
    if (strpos($fileToDelete, 'inbox/') === 0 && file_exists($fileToDelete)) {
        unlink($fileToDelete);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Parse function
function parseEmailContent($raw) {
    $headers = [];
    $body = '';
    $lines = preg_split("/\r\n|\n|\r/", $raw);
    $parsingHeaders = true;

    foreach ($lines as $line) {
        if ($parsingHeaders && trim($line) === '') {
            $parsingHeaders = false;
            continue;
        }

        if ($parsingHeaders) {
            if (stripos($line, 'From:') === 0) {
                $headers['from'] = trim(substr($line, 5));
            } elseif (stripos($line, 'To:') === 0) {
                $headers['to'] = trim(substr($line, 3));
            } elseif (stripos($line, 'Subject:') === 0) {
                $headers['subject'] = trim(substr($line, 8));
            }
        } else {
            $body .= $line . "\n";
        }
    }

    return [
        'from' => $headers['from'] ?? 'Unknown',
        'to' => $headers['to'] ?? 'Unknown',
        'subject' => $headers['subject'] ?? '(No Subject)',
        'body' => trim($body)
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Inbox Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .email {
            background: #fff;
            border-left: 5px solid #007BFF;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            position: relative;
        }
        .email .meta {
            margin-bottom: 15px;
        }
        .email .meta strong {
            display: inline-block;
            width: 80px;
        }
        .email .body {
            padding: 15px;
            background: #fdfdfd;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .delete-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<h2>📬 Inbox</h2>

<?php if (empty($files)): ?>
    <p>No emails yet.</p>
<?php else: ?>
    <?php foreach ($files as $file): ?>
        <?php
        $raw = file_get_contents($file);
        $email = parseEmailContent($raw);
        ?>
        <div class="email">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_file" value="<?= htmlspecialchars($file) ?>">
                <button class="delete-btn" onclick="return confirm('Delete this email?')">Delete</button>
            </form>
            <div class="meta">
                <p><strong>From:</strong> <?= htmlspecialchars($email['from']) ?></p>
                <p><strong>To:</strong> <?= htmlspecialchars($email['to']) ?></p>
                <p><strong>Subject:</strong> <?= htmlspecialchars($email['subject']) ?></p>
            </div>
            <div class="body">
                <?= $email['body'] ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
