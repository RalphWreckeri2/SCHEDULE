<?php
// Start the session
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
                <p>Please enter the reset token sent to your email. Make sure that it is the same.</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-container">
                <h2>Forgot Password</h2>
                <form method="post">
                    <div class="form-field">
                        <input type="text" name="token" placeholder="Reset Token" required>
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