<?php
include 'DbConnection.php';
session_start();

$error_message = '';
$success_message = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists in the database by name
    $query = "SELECT * FROM users WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, now check email and password
        $user = $result->fetch_assoc();
        if ($user['email'] === $email) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                $_SESSION['success_message'] = "Sign-in successful! <br> Welcome to Schedule, " . htmlspecialchars($name) . "."; // Set the success message
                header("Location: Dashboard.php"); // Redirect to Dashboard
                exit; // Ensure no further code is executed
            } else {
                $error_message = "Invalid password. Please try again.";
                $password = ''; // Clear password field
            }
        } else {
            $error_message = "Invalid email. Please try again.";
            $email = ''; // Clear email field
        }
    } else {
        $error_message = "You don't have an account. <a href='SignUp.php'>Create an account</a>";
        $name = ''; // Clear name field
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
    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            showSuccessMessage("<?php echo addslashes($_SESSION['success_message']); ?>");
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="container">
        <div class="panel-left">
            <div class="panel-content">
                <img src="SCHEDULE RBG.png" alt="SCHEDULE logo" class="logo">
                <div class="separator"></div>
                <p>Welcome back to Schedule - an Event Registration and Management System catered to make your social life hassle free!</p>
                <button class="btn btn-outline" onclick="window.location.href='AboutUs.html'">ABOUT US</button>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-container">
                <h2>Sign In</h2>
                <form method="post">
                    <div class="form-field">
                        <input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="form-field">
                        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="form-field">
                        <input type="password" name="password" placeholder="Password" value="" required>
                        <button type="button" id="togglePassword" class="toggle-password">Show</button>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const passwordInput = document.querySelector('input[name="password"]');
                                const togglePasswordButton = document.getElementById('togglePassword');

                                togglePasswordButton.addEventListener('click', function() {
                                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                                    passwordInput.setAttribute('type', type);

                                    // Toggle button text
                                    this.textContent = type === 'password' ? 'Show' : 'Hide';
                                });
                            });
                        </Script>
                    </div>
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary">SIGN IN</button>
                    </div>
                    <div class="forgot-password-wrapper">
                        <a href="EmailConfirmation.php" class="forgot-password">Forgot Password</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>