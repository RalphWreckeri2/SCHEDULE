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
                <p>Please make sure that the email you will input is the same email you use in this website. A reset token will be sent shortly.</p>
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