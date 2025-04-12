<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Send the reset email with the generated token
function sendResetEmail($email, $token)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ralphmatthew.samonte@gmail.com'; // Your email here
        $mail->Password = 'bavacgrxbihkwhla'; // Your email app password or SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ralphmatthew.samonte@gmail.com', 'SCHEDULE Support Team'); // Your email and name
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';

        // Send only the token in the email body
        $mail->Body = "Good Day Scheduler!<br><br>We received a request to reset your password. Your reset token is: <strong>$token</strong><br><br>If you did not request this, please ignore this email.<br><br>Thank you!<br>SCHEDULE Support Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "<script>alert('Mailer Error: " . addslashes($mail->ErrorInfo) . "'); window.location.href = 'EmailConfirmation.php';</script>";
        return false;
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in your users table
    $checkQuery = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a token (5 characters long)
        $token = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);

        if ($UserManager->InsertResetToken($email, $token)) {
            $emailSent = sendResetEmail($email, $token);

            if ($emailSent) {
                echo "<script>
                    alert('A reset token has been sent to your email. Please check your inbox.');
                    window.location.href = 'ResetTokenConfirmation.php?token=" . urlencode($token) . "';
                </script>";
            } else {
                echo "<script>
                    alert('Failed to send email. Please try again later.');
                </script>";
            }
        } else {
            echo "<script>alert('Failed to store reset token.')</script>";
        }
    }
}

$conn->close();
?>

<!-- HTML Form to Request Reset Token -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="panel-left">
            <div class="panel-content">
                <img src="SCHEDULE RBG.png" alt="SCHEDULE logo" class="logo">
                <div class="separator"></div>
                <p>Please make sure that the email you input is the same as the one you used on this website. A reset token will be sent to your email shortly.</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-container">
                <h2>Forgot Password</h2>
                <form method="post">
                    <div class="form-field">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary">SEND</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>