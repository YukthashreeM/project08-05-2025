<?php
session_start();
require 'vendor/autoload.php'; // Make sure Composer's autoloader is loaded
include 'db.php'; // Your DB connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(16));
    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Update token in the database
    $stmt = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
    $stmt->bind_param("sss", $token, $expiry, $email);
    if ($stmt->execute() && $stmt->affected_rows > 0) {

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com'; // your Gmail
            $mail->Password = 'your_app_password'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'Leave System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Link';
            $resetLink = "http://localhost/reset_password.php?token=" . urlencode($token);
            $mail->Body = "Click <a href='$resetLink'>here</a> to reset your password. This link will expire in 1 hour.";

            $mail->send();
            $message = "✅ Reset link sent to your email!";
        } catch (Exception $e) {
            $message = "❌ Email could not be sent. Error: " . $mail->ErrorInfo;
        }
    } else {
        $message = "❌ Email not found in our system.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Password Reset</title>
</head>
<body>
    <h2>Reset Password</h2>
    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required><br><br>
        <input type="submit" value="Send Reset Link">
    </form>
</body>
</html>
