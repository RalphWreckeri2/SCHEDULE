<?php
include 'DbConnection.php';
include 'CRUD.php'; // This is where your createUser() function lives

session_start();

$UserManager = new UserManager($conn); // Create an instance of UserManager
$showModal = false; // Flag to control modal display

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    if ($UserManager->InsertUser($name, $email, $phone, $password)) {
        $showModal = true; // Success modal or redirection flag
    } else {
        echo "<script>alert('Error: Could not create user.');</script>";
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
                        <input type="password" name="password" placeholder="Password" minlength="8" required>
                        <button type="button" id="togglePassword" class="toggle-password">Show</button>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const form = document.querySelector("form");
                                const passwordInput = document.querySelector('input[name="password"]');
                                const togglePasswordButton = document.getElementById('togglePassword');

                                // Prevent form submission on Enter key when min length of password is not met
                                form.addEventListener("submit", function(event) {
                                    if (passwordInput.value.length < 8) {
                                        event.preventDefault();
                                        alert("Password must be at least 8 characters long.");
                                    }
                                }); 

                                // Toggle password visibility
                                togglePasswordButton.addEventListener('click', function() {
                                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                                    passwordInput.setAttribute('type', type);

                                    // Toggle button text
                                    this.textContent = type === 'password' ? 'Show' : 'Hide';
                                });
                            });
                        </script>
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