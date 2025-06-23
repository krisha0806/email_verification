<?php
$host = "127.0.0.1";
$port = 1025;

$socket = stream_socket_server("tcp://$host:$port", $errno, $errstr);

if (!$socket) {
    die("Could not bind to socket: $errstr ($errno)\n");
}

echo "SMTP server running on $host:$port...\n";

while (true) {
    $conn = @stream_socket_accept($socket, 10); // 10-second timeout
    if (!$conn) {
        echo "No connection received. Waiting again...\n";
        continue;
    }

    fwrite($conn, "220 Fake SMTP Server Ready\r\n");

    $email = [
        'from' => '',
        'to' => '',
        'subject' => '',
        'body' => ''
    ];

    while ($line = fgets($conn)) {
        $line = trim($line);

        if (preg_match("/^HELO|EHLO/", $line)) {
            fwrite($conn, "250 Hello\r\n");
        } elseif (str_starts_with($line, "MAIL FROM:")) {
            $email['from'] = $line;
            fwrite($conn, "250 OK\r\n");
        } elseif (str_starts_with($line, "RCPT TO:")) {
            $email['to'] = $line;
            fwrite($conn, "250 OK\r\n");
        } elseif (str_starts_with($line, "DATA")) {
            fwrite($conn, "354 End data with <CR><LF>.<CR><LF>\r\n");
            $body = '';
            while (($dline = fgets($conn)) !== false) {
                if (trim($dline) === ".") break;
                $body .= $dline;
            }
            $email['body'] = $body;

            // Ensure inbox directory exists
            $inboxDir = __DIR__ . '/inbox';
            if (!is_dir($inboxDir)) {
                mkdir($inboxDir, 0777, true);
            }

            $file = $inboxDir . '/email_' . time() . '.txt';
            file_put_contents($file, print_r($email, true));

            fwrite($conn, "250 OK: Message received\r\n");
        } elseif ($line === "QUIT") {
            fwrite($conn, "221 Bye\r\n");
            break;
        } else {
            fwrite($conn, "250 OK\r\n");
        }
    }

    fclose($conn);
}
