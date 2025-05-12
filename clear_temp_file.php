<?php
// clear_temp_file.php
session_start();

// Check if there's a temp file to delete
if (isset($_SESSION['temp_photo_path']) && file_exists($_SESSION['temp_photo_path'])) {
    // Delete the temporary file
    unlink($_SESSION['temp_photo_path']);
}

// Clear the session variables
unset($_SESSION['temp_photo_path']);
unset($_SESSION['temp_photo_name']);

// Return success
header('Content-Type: application/json');
echo json_encode(['success' => true]);
