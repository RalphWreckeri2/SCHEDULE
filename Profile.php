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
            <img src="SCHEDULE RBG.png" alt="Schedule Logo" class="logo1">
        </div>
        
        <div class="separator"></div>
        
        <div class="nav-menu">
            <a href="Dashboard.php" class="nav-item" id="dashboard">
                <img src="dashboard-icon.png" alt="Dashboard" class="nav-icon">
                <span>Dashboard</span>
            </a>
            <a href="MyEvents.php" class="nav-item" id="my-events">
                <img src="my-events-icon.png" alt="My Events" class="nav-icon">
                <span>My Events</span>
            </a>
            <a href="NewEvent.php" class="nav-item" id="new-event">
                <img src="new-event-icon.png" alt="New Event" class="nav-icon">
                <span>New Event</span>
            </a>
            <a href="AvailableEvent.php" class="nav-item" id="available-events">
                <img src="available-events-icon.png" alt="Available Events" class="nav-icon">
                <span>Available Events</span>
            </a>
        </div>

        <div class="bottom-menu">
            <a href="Profile.php" class="nav-item" id="profile">
                <img src="profile-icon.png" alt="Profile" class="nav-icon">
                <span>Profile</span>
            </a>
            <a href="Logout.php" class="nav-item" id="logout">
                <img src="logout-icon.png" alt="Log Out" class="nav-icon">
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

            <!-- Profile Section (php din dine fetch fetch na lang - sample pic mukha q kasi why not)-->
            <div class="profile-wrapper">
                <div class="profile-pic-wrapper">
                    <img src="samonte.png" alt="Profile Picture" class="profile-pic">
                </div>
                <div class="profile-info-wrapper">
                    <h2 class="profile-name">Name</h2>
                    <p class="profile-details">Email here</p>
                    <p class="profile-details">Phone Number</p>
                </div>
                <div class="profile-edit-wrapper">
                    <button class="edit-button">Edit Profile Picture</button>
            </div>
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
        
        // Add click event listeners for navigation within the same page
        navItems.forEach(function(item) {
        item.addEventListener('click', function() {
            // We don't need to do anything here since the page will reload
            // and the above code will set the correct active state
        });
        });
        });
    </script>
    
</body>
</html>