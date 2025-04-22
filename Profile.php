<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $profile = $UserManager->ProfileFetcher($user_id);

    if (!$profile) {
        error_log("Profile data not found for user_id: $user_id");
        $profile = ['user_profile' => 'photos/profile-icon.png', 'name' => 'Name', 'email' => 'Email', 'phone' => 'Phone Number'];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];

    $targetDir = "uploads/";
    if (isset($_FILES["user_profile"]) && $_FILES["user_profile"]["error"] == UPLOAD_ERR_OK) {
        $imageName = basename($_FILES["user_profile"]["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["user_profile"]["tmp_name"], $targetFile)) {
            $user_profile = $targetFile;
            $UserManager->UpdateProfile($user_id, $user_profile);
            // Reload profile info after update
            $profile = $UserManager->ProfileFetcher($user_id);

            // Add success message
            echo "<script>alert('Profile picture updated successfully!');</script>";
        } else {
            echo "<script>alert('Error uploading image.');</script>";
        }
    } else if (isset($_FILES["user_profile"]) && $_FILES["user_profile"]["error"] != UPLOAD_ERR_NO_FILE) {
        // Only show error if a file was selected but had an error (not when no file is selected)
        echo "<script>alert('Error uploading image: " . $_FILES["user_profile"]["error"] . "');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Events</title>
    <link rel="stylesheet" href="styles1.css">
</head>

<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="logo-section">
            <img src="photos/SCHEDULE RBG.png" alt="Schedule Logo" class="logo1">
        </div>

        <div class="separator"></div>

        <div class="nav-menu">
            <a href="Dashboard.php" class="nav-item" id="dashboard">
                <img src="photos/dashboard-icon.png" alt="Dashboard" class="nav-icon">
                <span>Dashboard</span>
            </a>
            <a href="MyEvents.php" class="nav-item" id="my-events">
                <img src="photos/my-events-icon.png" alt="My Events" class="nav-icon">
                <span>My Events</span>
            </a>
            <a href="NewEvent.php" class="nav-item" id="new-event">
                <img src="photos/new-event-icon.png" alt="New Event" class="nav-icon">
                <span>New Event</span>
            </a>
            <a href="AvailableEvent.php" class="nav-item" id="available-events">
                <img src="photos/available-events-icon.png" alt="Available Events" class="nav-icon">
                <span>Available Events</span>
            </a>
        </div>

        <div class="bottom-menu">
            <a href="Profile.php" class="nav-item" id="profile">
                <img src="photos/profile-icon.png" alt="Profile" class="nav-icon">
                <span>Profile</span>
            </a>
            <a href="Logout.php" class="nav-item" id="logout">
                <img src="photos/logout-icon.png" alt="Log Out" class="nav-icon">
                <span>Log Out</span>
            </a>
        </div>


    </div>

    <!-- Main Content Area -->
    <div class="profile-main-content">
        <div class="profile-in-main-content">

            <!--<h2 class="profile-heading">Profile</h2>
            <p class="description">
                Welcome to your profile page! Here you can view and edit your personal information, manage your events, and access support.
            </p>-->

            <!-- Profile Section -->
            <form method="post" enctype="multipart/form-data" id="profileForm">
                <div class="profile-wrapper">
                    <div class="profile-pic-wrapper">
                        <img src="<?php echo htmlspecialchars($profile['user_profile']); ?>" alt="Profile Picture" class="profile-pic" id="profilePreview">
                    </div>
                    <div class="profile-info-wrapper">
                        <h2 class="profile-name"><?php echo htmlspecialchars($profile['name']); ?></h2>
                        <p class="profile-details"><?php echo htmlspecialchars($profile['email']); ?></p>
                        <p class="profile-details"><?php echo htmlspecialchars($profile['phone']); ?></p>
                    </div>
                    <div class="profile-edit-wrapper">
                        <button class="edit-button" type="button" id="triggerUpload">Edit Profile Picture</button>
                        <input type="file" name="user_profile" id="hiddenUpload" accept="image/*" style="display: none;">
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all navigation items
            const navItems = document.querySelectorAll('.nav-item');

            // Get current page URL
            const currentPage = window.location.pathname;

            // Remove 'active' class from all navigation items
            navItems.forEach(function(item) {
                item.classList.remove('active');
            });

            // Find which nav item matches the current page and set it as active
            navItems.forEach(function(item) {
                // Get the href attribute
                const href = item.getAttribute('href');

                // Extract just the filename from the href
                const hrefPage = href.split('/').pop();

                // Extract just the filename from the current URL
                const currentPageName = currentPage.split('/').pop();

                // Check if this nav item corresponds to the current page
                if (currentPageName === hrefPage ||
                    (currentPageName === 'Dashboard.php' && item.id === 'dashboard') ||
                    (currentPageName === '' && item.id === 'dashboard')) {
                    item.classList.add('active');
                    console.log('Set active:', item.id);
                }
            });

            // Profile picture upload functionality
            const triggerUploadBtn = document.getElementById('triggerUpload');
            const hiddenUpload = document.getElementById('hiddenUpload');
            const profileForm = document.getElementById('profileForm');
            const profilePreview = document.getElementById('profilePreview');

            // Trigger file input when button is clicked
            triggerUploadBtn.addEventListener('click', function() {
                hiddenUpload.click();
            });

            // Submit form when a file is selected
            hiddenUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    // Show preview of the selected image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(this.files[0]);

                    // Submit the form
                    profileForm.submit();
                }
            });
        });
    </script>

</body>

</html>