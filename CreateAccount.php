<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    // Here you would typically:
    // 1. Validate the input
    // 2. Hash the password
    // 3. Store in database
    // 4. Handle the registration process
    
    // For now, we'll just redirect back with a success message
    header("Location: index.php?success=1");
    exit;
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
</body>
</html>