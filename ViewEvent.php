<?php 
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $event = $UserManager->GetEvents($user_id);
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
    <div class="view-event-main-content">
        <div class="view-event-in-main-content">
         
            <div class="header">
                <h1>Event Details</h1>
                <div class="separator"></div>
            </div>

            <div class="event-wrapper">
                <div class="event-top-section">
            
                    <!-- LEFT: Event Photo -->
                    <div class="event-photo-container">
                        <img src="event-photo.jpg" alt="Event Photo" class="event-photo">
                    </div>

                    <!-- RIGHT: Event Details -->
                    <div class="event-details-container">
                        <h2>Event Name</h2>
                        <p><strong>Category: </strong>Event Category</p>
                        <p><strong>Slots: </strong>Event Slots</p>
                        <p><strong>Status: </strong>Event Status</p>
                        <p><strong>Description: </strong>This is a sample event description.</p>
                        <p><strong>Date: </strong> Event Date</p>
                        <p><strong>Time: </strong> Event Time</p>
                        <p><strong>Location: </strong>Event Location</p>
                        <p><strong>Speaker: </strong>Event Speaker</p>
                    </div>
                    
                </div>
            </div>

                <!-- BOTTOM: Buttons -->
            <div class="view-event-button-container">
                <button class="delete-event-button">Delete</button>
                <button class="edit-event-button">Edit</button>
                <button class="participant-event-button">Participants</button>
                <button class="confirm-event-button">Confirm</button>            
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
        if (
            currentPageName === hrefPage || 
            (currentPageName === 'Dashboard.php' && item.id === 'dashboard') ||
            (currentPageName === '' && item.id === 'dashboard') ||
            (currentPageName === 'ViewEvent.php' && item.id === 'my-events')
        ) {
            item.classList.add('active');
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