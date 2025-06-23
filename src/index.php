<?php
require_once 'functions.php';

// Initialize variables
$registration_message = '';
$verification_message = '';
$email = '';
$verification_code = '';

// Directory to store verification codes
$codeDir = __DIR__ . '/verification_codes';
if (!file_exists($codeDir)) {
    mkdir($codeDir, 0777, true);
}

// Handle email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $email = trim($_POST['email']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        $codeFile = "$codeDir/register_" . md5($email) . ".txt";

        if (file_put_contents($codeFile, $code) !== false) {
            if (sendVerificationEmail($email, $code)) {
                $registration_message = "✅ Verification code sent to $email.";
            } else {
                $registration_message = "❌ Failed to send verification email.";
            }
        } else {
            $registration_message = "❌ Failed to store verification code.";
        }
    } else {
        $registration_message = "❌ Invalid email address.";
    }
}

// Handle verification code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    $email = trim($_POST['email']);
    $code_input = trim($_POST['verification_code']);
    $codeFile = "$codeDir/register_" . md5($email) . ".txt";

    if (file_exists($codeFile)) {
        $stored_code = trim(file_get_contents($codeFile));
        if ($code_input === $stored_code) {
            registerEmail($email);
            unlink($codeFile);
            $verification_message = "✅ Email verified and registered successfully.";
        } else {
            $verification_message = "❌ Invalid verification code.";
        }
    } else {
        $verification_message = "❌ No verification code found for this email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GH-timeline Registration</title>
</head>
<body>
    <h2>Subscribe to GitHub Timeline</h2>

    <!-- Display messages -->
    <p><?php echo htmlspecialchars($registration_message); ?></p>
    <p><?php echo htmlspecialchars($verification_message); ?></p>

    <!-- Email Registration Form -->
    <form method="POST">
        <input type="hidden" name="action" value="register">
        <label for="email">Email:</label><br>
        <input type="email" name="email" required><br>
        <button type="submit">Send Verification Code</button>
    </form>

    <br><hr><br>

    <!-- Verification Code Form -->
    <form method="POST">
        <input type="hidden" name="action" value="verify">
        <label for="email">Email:</label><br>
        <input type="email" name="email" required><br>
        <label for="verification_code">Verification Code:</label><br>
        <input type="text" name="verification_code" maxlength="6" required><br>
        <button type="submit">Verify</button>
    </form>

    <br><hr><br>

    <h3>Inbox Viewer</h3>
    <button onclick="window.open('http://localhost:8000/mailManager/web-ui.php', '_blank')">
        Open Inbox Viewer
    </button>
</body>
</html>
