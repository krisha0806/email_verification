<?php
require_once 'functions.php';

// Initialize messages
$unsubscribe_message = '';
$unsubscribe_verify_message = '';
$unsubscribe_dir = __DIR__ . '/verification_codes';

if (!file_exists($unsubscribe_dir)) {
    mkdir($unsubscribe_dir, 0777, true);
}

$email = '';
$step = 1;

// Handle unsubscription code request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Request verification code
    if (isset($_POST['request_unsubscribe'])) {
        $email = trim($_POST['unsubscribe_email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            file_put_contents("$unsubscribe_dir/unsub_" . md5($email) . ".txt", $code);
            $subject = "Confirm Unsubscription";
            $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";
            $headers = "From: no-reply@example.com\r\n";
            $headers .= "Content-Type: text/html\r\n";
            if (mail($email, $subject, $message, $headers)) {
                $unsubscribe_message = "✅ A confirmation code has been sent to $email.";
                $step = 2;
            } else {
                $unsubscribe_message = "❌ Failed to send confirmation code.";
            }
        } else {
            $unsubscribe_message = "❌ Invalid email address.";
        }
    }

    // Step 2: Verify the code
    if (isset($_POST['verify_unsubscribe'])) {
        $email = trim($_POST['unsubscribe_email']);
        $code_input = trim($_POST['unsubscribe_verification_code']);
        $code_file = "$unsubscribe_dir/unsub_" . md5($email) . ".txt";

        if (file_exists($code_file)) {
            $stored_code = trim(file_get_contents($code_file));
            if ($code_input === $stored_code) {
                if (unsubscribeEmail($email)) {
                    unlink($code_file);
                    $unsubscribe_verify_message = "✅ You have been successfully unsubscribed.";
                    $step = 1;
                } else {
                    $unsubscribe_verify_message = "❌ Unsubscription failed or email not found.";
                    $step = 1;
                }
            } else {
                $unsubscribe_verify_message = "❌ Incorrect verification code.";
                $step = 2;
            }
        } else {
            $unsubscribe_verify_message = "❌ No verification code found for this email.";
            $step = 1;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe from GitHub Timeline</title>
</head>
<body>
    <h2>Unsubscribe from GitHub Timeline</h2>

    <!-- Messages -->
    <p><?= htmlspecialchars($unsubscribe_message) ?></p>
    <p><?= htmlspecialchars($unsubscribe_verify_message) ?></p>

    <?php if ($step === 1): ?>
        <!-- Request Unsubscribe Code -->
        <form method="POST">
            <label for="unsubscribe_email">Enter your email:</label><br>
            <input type="email" name="unsubscribe_email" required><br>
            <button type="submit" name="request_unsubscribe">Send Verification Code</button>
        </form>
    <?php elseif ($step === 2): ?>
        <!-- Enter Verification Code -->
        <form method="POST">
            <input type="hidden" name="unsubscribe_email" value="<?= htmlspecialchars($email) ?>">
            <label for="unsubscribe_verification_code">Enter verification code sent to <?= htmlspecialchars($email) ?>:</label><br>
            <input type="text" name="unsubscribe_verification_code" maxlength="6" required><br>
            <button type="submit" name="verify_unsubscribe">Confirm Unsubscribe</button>
        </form>
    <?php endif; ?>
</body>
</html>
