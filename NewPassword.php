<?php
include 'DbConnection.php';
include 'CRUD.php';
session_start();
$UserManager = new UserManager($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    $email = $_SESSION['reset_email']; // Get the email from session

    if ($new_password == $confirm_new_password) {
        $hash_password = password_hash($new_password, PASSWORD_DEFAULT);
        if ($UserManager->UpdatePassword($email, $hash_password)) {
            echo "<script>alert('Password updated successfully!');
            window.location.href = 'SignIn.php';</script>"; 
        } else {
            echo "<script>alert('Error updating password. Please try again.');</script>";
        };
    } else {
        echo "<script>alert('Failed to update password. Passwords should match!');
        window.location.href = 'NewPasssword.php' ;</script>";
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Create Account</title>
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
                <p>Please enter your new password. Make sure they are the same. Do not forget it again Scheduler!</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-container">
                <h2>Forgot Password</h2>
                <form method="post">
                    <div class="form-field">
                        <input type="password" name="new_password" placeholder="New Password" required>
                        <button type="button" class="toggle-password" data-target="new_password">Show</button>
                    </div>
                    <div class="form-field">
                        <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required>
                        <button type="button" class="toggle-password" data-target="confirm_new_password">Show</button>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary">CONFIRM</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleButtons = document.querySelectorAll(".toggle-password");

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute("data-target");
                    const targetInput = document.querySelector(`input[name="${targetId}"]`);

                    if (targetInput) {
                        const type = targetInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        targetInput.setAttribute("type", type);
                        this.textContent = type === 'password' ? 'Show' : 'Hide';
                    }
                });
            });
        });
    </script>
</body>

</html>