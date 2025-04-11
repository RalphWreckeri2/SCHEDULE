<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

// Create a new PHPMailer instance
$mail = new PHPMailer\PHPMailer\PHPMailer();

// Set the mailer to use SMTP
$mail->isSMTP();

// SMTP server configuration
$mail->Host = 'smtp.gmail.com'; // For example, using Mailtrap for testing
$mail->SMTPAuth = true;
$mail->Username = 'ralphmatthew.samonte@gmail.com'; // Your SMTP username
$mail->Password = 'bavacgrxbihkwhla'; // Your SMTP password
$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

// Set sender and recipient details
$mail->setFrom('ralphmatthew.samonte@gmail.com', 'Mailer');
$mail->addAddress('ruffaanoya2005@gmail.com', 'Ruffa Test User'); // Add a recipient

// Set email format to HTML
$mail->isHTML(true);
$mail->Subject = 'Test Email from PHPMailer';
$mail->Body    = 'This is a test email sent using PHPMailer!';

// Send the email
if($mail->send()) {
    echo 'Message has been sent successfully!';
} else {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}
?>
