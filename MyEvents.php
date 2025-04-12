<?php
include 'DbConnection.php';
include 'CRUD.php'; // This is where your CRUD functions live

session_start();
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
    <div class="main-content">
        <div class="information-in-main-content">

            <div class="header">
                <h1>My Events</h1>
                <p>Effortlessly manage and track all your created events in one place.</p>
            </div>

            <div class="separator-line"></div>
            
            <h2>Events You Created</h2>
            <p class="description">
                Browse and manage your events with ease—filter them by status!
            </p>

            <div class="search-bar">
                <input type="text" placeholder="Search events..." class="search-input">
                <button class="search-button">Search</button>
            </div>

            <div class="filter-container">
                <select id="filter" class="filter-select" onchange="applyFilter(this.value)">
                    <option value="all">All</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="current">Current</option>
                    <option value="past">Past</option>
                </select>
                <script>
                    function applyFilter(filterValue) {
                        console.log('Filter applied:', filterValue);
                        // Add your logic here to handle the filter change
                    }
                </script>
            </div>
            
            <!--need rin pala ng ui for a user na wlaa pang events... pero condition yun sa backend eh HAHA-->
            <!-- php logic here to show the events created by the user, view button leads to ViewEvent -->
            <div class="event-panel-container">
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="view-button">View</button></div>
                </div>
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="view-button">View</button></div>
                </div>
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="view-button">View</button></div>
                </div>
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="view-button">View</button></div>
                </div>
            </div>
           
            <!--No need na maglagay ng View all options kasi dito na lahat naklagay ng events-->
            
            <div class="separator-line"></div>

            <!--Dito ay anchor tags ang paglalagay sa event titles (need kasi clickable) tas kahit par na lang sa date, pagclick sa View, ViewEvent rin ang punta-->

            <h2>Events You Joined</h2>
            <p class="description">Browse and manage your events with ease—segregated by status!</p>
            
            <h3 class="description-heading">Your Upcoming Events</h3>
            <p class="description">Explore new learning opportunities and gain valuable skills!</p>
            <div class="upcoming-events-container">
                <div class="upcoming-events-labels">
                    <p class="label">Event Title</p>
                    <p class="label">Date</p>
                </div>
            </div>

            <h3 class="description-heading">Your Past Events</h3>
            <p class="description">Rekindle the details of your past events!</p>
            <div class="past-events-container">
            <div class="past-events-labels">
                    <p class="label">Event Title</p>
                    <p class="label">Date</p>
                </div>
            </div>

            <div class="separator-line"></div>

            <h2>Contact Us</h2>
            <p class="description">
                Have questions or need assistance? We're here to help! Feel free to reach out to us for any inquiries about event registrations, technical support, or general concerns.
            </p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <img src="address-icon.png" alt="Address" class="contact-icon">
                    <div class="contact-text">
                        <strong>Address:</strong> 1234 Rizal Street, Makati City, Metro Manila, Philippines
                    </div>
                </div>
                
                <div class="contact-item">
                    <img src="email-icon.png" alt="Email" class="contact-icon">
                    <div class="contact-text">
                        <strong>Email:</strong> support@scheduleevents.ph
                    </div>
                </div>
                
                <div class="contact-item">
                    <img src="phone-icon.png" alt="Phone" class="contact-icon">
                    <div class="contact-text">
                        <strong>Phone:</strong> (+63) 912-345-6789
                    </div>
                </div>
                
                <div class="contact-item">
                    <img src="social-icon.png" alt="Socials" class="contact-icon">
                    <div class="contact-text">
                        <strong>Socials:</strong> facebook.com/scheduleeventsph | twitter.com/scheduleeventsph
                    </div>
                </div>
            </div>

            <p class="social-text">
                You can also follow us on our social media channels for updates and announcements!
            </p>
            
            <p class="copyright">All Rights Reserved. 2025</p>
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