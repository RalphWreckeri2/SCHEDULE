<?php
include 'DbConnection.php';
session_start();
$showModal = false; // Flag to control modal display

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        $showModal = true; // Set flag to true to show modal
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
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
                <h1>Welcome Back!</h1>
                <div class="separator"></div>
                <p>To keep connected with us, please login with your personal information.</p>
                <button class="btn btn-outline" onclick="window.location.href='SignIn.php'">SIGN IN</button>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-container">
                <h2>Create Account</h2>
                <form method="post">
                    <div class="form-field">
                        <input type="text" name="name" placeholder="Name" required>
                    </div>
                    <div class="form-field">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-field">
                        <input type="tel" name="phone" placeholder="Phone Number" required>
                    </div>
                    <div class="form-field">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary">SIGN UP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h2>Account Created Successfully!</h2>
            <p>Please sign in to continue.</p>
            <button onclick="redirectToSignIn()">Go to Sign In</button>
        </div>
    </div>

    <script>
        // Function to redirect to the sign-in page
        function redirectToSignIn() {
            window.location.href = 'SignIn.php';
        }

        // Show the modal if the PHP flag is set
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($showModal): ?>
                document.getElementById('successModal').style.display = 'block';
            <?php endif; ?>
        });
    </script>
</body>

</html>