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
                <h1>Welcome to Schedule Events</h1>
                <p>Your one-stop solution for managing and scheduling events.</p>
            </div>

            <div class="separator-line"></div>
            
            <h2>Explore New Events</h2>
            <p class="description">
                Discover a variety of events happening around you. Join us to learn, network, and grow!
            </p>

            <!-- Event Panels (lalagyan ng php dine - select keme -- pede ding i for loop na lang sya tas same formats haha) -->
            <div class="event-panel-container">
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="register-button">Register Now</button></div>
                </div>
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="register-button">Register Now</button></div>
                </div>
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="register-button">Register Now</button></div>
                </div>
                <div class="event-panel">
                    <img src="event1.jpg" alt="Event 1" class="event-image">
                    <h3>Event Title 1</h3>
                    <p class="event-category">Event Category</p>
                    <p class="event-slots">Slots Available</p>
                    <p class="event-description">Join us for an exciting event that will enhance your skills and knowledge.</p>
                    <div class="button-wrapper"><button class="register-button">Register Now</button></div>
                </div>
            </div>

            <div class="view-more-button-container">
                <img src="view-more-icon.png" alt="More" class="view-more-icon">
                <a href="AvailableEvent.php" class="view-more-button">View More Events</a>
            </div>
            <!--<div class="separator-line"></div>-->

            <h2>Your Upcoming Events</h2>
            <p class="description">
                Explore new learning opportunities and gain valuable skills!
            </p>

            <!-- EVent details -- php rin ito (including yung format ng separator line) pagkaclick ng view, puntang ViewEvent-->
            <div class="upcoming-events-container">
                <div class="upcoming-events-labels">
                    <p class="label">Event Title</p>
                    <p class="label">Date</p>
                </div>
                <div class="button-wrapper"><button class="view-button" onclick="window.location.href='MyEvents.php'">View Details</button></div>
            </div>
           
            <h2>About Us</h2>
                <p class="description">Schedule</p>
            
            <div class="about-us-section">
                <h3 class="section-title">What is <span class="highlight">Schedule</span>?</h3>
                <p class="about-us">Schedule is a web-based event registration system designed to streamline the process of managing and organizing events. It provides a user-friendly interface for both event organizers and participants, making it easy to create, register, and manage events online.</p>
                
                <h3 class="section-title">Our Mission</h3>
                <p class="about-us">To provide an organized, digital solution for event registration that enhances accessibility, reduces manual effort, and promotes eco-friendly, paperless event management.</p>
                
                <h3 class="section-title">What We Offer</h3>
                <ul class="about-list">
                    <li><strong>Easy Event Registration:</strong> Browse and sign up for events with just a few clicks.</li>
                    <li><strong>Effortless Event Management:</strong> Organizers can create and monitor events efficiently.</li>
                    <li><strong>Eco-Friendly Approach:</strong> Reducing paper-based event registrations.</li>
                </ul>
            </div>

            <div class="separator-line"></div>
            
            <h2>Developed by:</h2>
            <p class="description">
                Meet the talented team behind Schedule Events!
            </p>

            <div class="developers">
                <div class="developer-info">
                    <img src="de-guzman.png" alt="Developer 1" class="developer-image">
                    <p class="developer-name">James Patrick S. De Guzman</p>
                    <p class="developer-description">Group Leader</p>
                </div>
                <div class="developer-info">
                    <img src="samonte.png" alt="Developer 2" class="developer-image">
                    <p class="developer-name">Ralph Matthew A. Samonte</p>
                    <p class="developer-description">Assistant Leader</p>
                </div>
                <div class="developer-info">
                    <img src="gonito.png" alt="Developer 3" class="developer-image">
                    <p class="developer-name">Drred Klain M, Gonito</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="cortiguerra.png" alt="Developer 4" class="developer-image">
                    <p class="developer-name">Vinz Emmanuel D. Cortiguerra</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="montes.png" alt="Developer 5" class="developer-image">
                    <p class="developer-name">Kenneth E. Montes</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="protestante.png" alt="Developer 6" class="developer-image">
                    <p class="developer-name">Louisa Victoria C. Protestante</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="tucay.png" alt="Developer 7" class="developer-image">
                    <p class="developer-name">Alexander James L. Tucay</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="macaraig.png" alt="Developer 8" class="developer-image">
                    <p class="developer-name">Angiela D. Macaraig</p>
                    <p class="developer-description">Group Member</p>
                </div>
            </div>

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