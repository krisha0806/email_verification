<?php

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    ini_set("SMTP", "localhost");
    ini_set("smtp_port", "1025");
    ini_set("sendmail_from", "no-reply@example.com");

    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html\r\n";

    $success = mail($email, $subject, $message, $headers);

    if (!$success) {
        file_put_contents(__DIR__ . '/failed_emails.txt', "$email | " . date('c') . PHP_EOL, FILE_APPEND);
        error_log("❌ sendVerificationEmail failed for $email\n", 3, __DIR__ . '/email_errors.log');
    } else {
        file_put_contents(__DIR__ . '/sent_emails.txt', "$email | " . date('c') . PHP_EOL, FILE_APPEND);
    }

    return $success;
}


/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        file_put_contents($file, "");
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
        return true;
    }
    return false;
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = array_filter($emails, fn($e) => trim($e) !== trim($email));

    if (count($updated) < count($emails)) {
        file_put_contents($file, implode(PHP_EOL, $updated) . PHP_EOL, LOCK_EX);
        return true;
    }

    return false;
}

/**
 * Fetch GitHub timeline.
 */
function fetchGitHubTimeline() {
    // This is a placeholder since https://www.github.com/timeline doesn't exist.
    // Replace with actual working API or simulate data.
    $mockData = [
        ['event' => 'Push', 'user' => 'testuser'],
        ['event' => 'Fork', 'user' => 'anotheruser']
    ];
    return $mockData;
}

/**
 * Format GitHub timeline data. Returns a valid HTML string.
 */
function formatGitHubData(array $data): string {
    $html = '<h2>GitHub Timeline Updates</h2>';
    $html .= '<table border="1"><tr><th>Event</th><th>User</th></tr>';

    foreach ($data as $entry) {
        $html .= "<tr><td>{$entry['event']}</td><td>{$entry['user']}</td></tr>";
    }

    $html .= '</table>';
    return $html;
}

/**
 * Send the formatted GitHub updates to registered emails.
 */

function sendGitHubUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    ini_set("SMTP", "localhost");
    ini_set("smtp_port", "1025");
    ini_set("sendmail_from", "no-reply@example.com");

    if (!file_exists($file)) {
        return;
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = fetchGitHubTimeline();

    if (!$data) return;

    $formattedHtml = formatGitHubData($data);

    $subject = "Latest GitHub Updates";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html\r\n";
    

    foreach ($emails as $email) {
        $unsubscribe_link = 'http://yourdomain.com/unsubscribe.php?email=' . urlencode($email);
        $body = $formattedHtml;
        $body .= '<p><a href="' . $unsubscribe_link . '" id="unsubscribe-button">Unsubscribe</a></p>';
        $unsubscribe_link = 'http://localhost:8000/mailManager/unsubscribe.php?email=' . urlencode($email);
        $body .= '<p><a href="' . $unsubscribe_link . '">Unsubscribe</a></p>';

        mail($email, $subject, $body, $headers);
    }
}
