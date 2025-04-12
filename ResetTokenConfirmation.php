<?php
include 'DbConnection.php';
session_start();

// Check if token is in URL or form submission
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // Store token in session so it's available after form submission
    $_SESSION['reset_token'] = $token;
} elseif (isset($_POST['token'])) {
    $token = $_POST['token'];
} elseif (isset($_SESSION['reset_token'])) {
    $token = $_SESSION['reset_token'];
} else {
    echo "<script>alert('No token provided.'); window.location.href = 'ForgotPassword.php';</script>";
    exit();
}

// If form was submitted, verify the token
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    // Proceed with your logic here, checking the token in the database
    $sql = "SELECT * FROM password_resets WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $created_at = strtotime($row['created_at']);
        $expires_at = $created_at + (5 * 60); // Token expires after 5 minutes

        if (time() <= $expires_at) {
            // Token is valid, allow password reset
            $_SESSION['reset_email'] = $row['email'];
            header("Location: NewPassword.php"); // Make sure this is the correct file name
            exit();
        } else {
            echo "<script>alert('Token has expired. Please request a new one.'); window.location.href = 'ForgotPassword.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid token.'); window.location.href = 'ForgotPassword.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
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
                <p>Please enter the reset token sent to your email. Make sure that it is the same.</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-container">
                <h2>Verify Reset Token</h2>
                <form method="post">
                    <div class="form-field">
                        <input type="text" name="token" placeholder="Reset Token" value="<?php echo htmlspecialchars($token ?? ''); ?>" required>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary">VERIFY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>