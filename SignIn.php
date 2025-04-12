<?php
include 'DbConnection.php';
include 'CRUD.php';

session_start();

$UserManager = new UserManager($conn);
$error_message = '';
$success_message = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $UserManager->AuthenticateUser($name, $email, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['success_message'] = "Sign-in successful! <br> Welcome to Schedule, " . htmlspecialchars($name) . "."; // Set the success message
        header('Location: Dashboard.php');
        exit;
    } else {
        $error_message = "Invalid credentials. Please try again.";
        $name = ''; // Clear name field
        $email = ''; // Clear email field   
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