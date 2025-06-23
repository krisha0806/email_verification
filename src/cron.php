<?php
require_once 'functions.php';

function fetchGitHubTimeline(): array {
    $url = 'https://api.github.com/events';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: GitHub-Timeline-Script'
            ]
        ]
    ]);

    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        return [];
    }

    $events = json_decode($response, true);
    $result = [];

    foreach ($events as $event) {
        $result[] = [
            'event' => $event['type'] ?? 'UnknownEvent',
            'user'  => $event['actor']['login'] ?? 'unknown'
        ];
    }

    return $result;
}


// Format as HTML
function formatGitHubData(array $data): string {
    $html = '<h2>GitHub Timeline (Latest)</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<tr><th>Event</th><th>User</th></tr>';

    foreach ($data as $entry) {
        $html .= "<tr><td>{$entry['event']}</td><td>{$entry['user']}</td></tr>";
    }

    $html .= '</table>';
    return $html;
}

// Main mailer logic
function sendGitHubUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) return;

    $data = fetchGitHubTimeline();
    $htmlBody = formatGitHubData($data);

    $subject = "⏰ GitHub Timeline Update";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html\r\n";

    foreach ($emails as $email) {
        $unsubscribe_link = 'http://localhost/unsubscribe.php?email=' . urlencode($email);
        $body = $htmlBody . "<p><a href='$unsubscribe_link'>Unsubscribe</a></p>";

        ini_set("SMTP", "localhost");
        ini_set("smtp_port", "1025");

        if (!mail($email, $subject, $body, $headers)) {
            file_put_contents(__DIR__ . '/failed_emails.txt', "$email|" . time() . PHP_EOL, FILE_APPEND);
        }
    }
}

sendGitHubUpdatesToSubscribers();

