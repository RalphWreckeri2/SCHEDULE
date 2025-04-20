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
    <style>
        /* Loading modal styles */
        .loading-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgb(255, 255, 255);
            display: none; /* Initially hidden */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-modal .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #74b1f4;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
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