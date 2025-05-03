<?php
session_start(); // Start the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <script>
        // Ask the user for confirmation before logging out
        if (confirm("Are you sure you want to log out?")) {
            // Show the loading modal
            const modal = document.createElement('div');
            modal.className = 'loading-modal';
            modal.innerHTML = `
                <div class="spinner"></div>
                <p style="color: #333; font-size: 18px; margin-top: 10px; margin-left: 10px">Logging out...</p>
            `;
            document.body.appendChild(modal);
            modal.style.display = 'flex';

            // Redirect to the login page after 3 seconds
            setTimeout(function() {
                window.location.href = "SignIn.php";
            }, 3000);
        } else {
            // Redirect back to the dashboard or previous page
            window.location.href = "Dashboard.php";
        }
    </script>
</body>

</html>