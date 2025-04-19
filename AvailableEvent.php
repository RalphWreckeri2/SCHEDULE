<?php
include 'DbConnection.php';
include 'CRUD.php';
$UserManager = new UserManager($conn);

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $event_category = isset($_POST['filter']) ? $_POST['filter'] : 'all'; // Default to 'all' if not set
    $events = [];
    $events = $UserManager->EventFetcher($user_id, $event_category);
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
    <div class="main-content">
        <div class="information-in-main-content">

            <div class="header">
                <h1>Available Events</h1>
                <p>Discover upcoming events, seminars, and workshops. Browse the full list and join the ones that interests you!.</p>
            </div>

            <div class="separator-line"></div>

            <h2>Event Categories</h2>
            <p class="description">
                Personalize what you see! Choose what is best suited for your taste.
            </p>

            <div class="search-bar">
                <input type="text" placeholder="Search events..." class="search-input">
                <button class="search-button">Search</button>
            </div>

            <form method="post" action="">
                <div class="filter-container">
                    <select id="filter" name="filter" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo ($event_category === "all") ? "selected" : ""; ?>>All</option>
                        <option value="Business & Finance" <?php echo ($event_category === "Business & Finance") ? "selected" : ""; ?>>Business & Finance</option>
                        <option value="Technology & Innovation" <?php echo ($event_category === "Technology & Innovation") ? "selected" : ""; ?>>Technology & Innovation</option>
                        <option value="Health & Wellness" <?php echo ($event_category === "Health & Wellness") ? "selected" : ""; ?>>Health & Wellness</option>
                        <option value="Personal & Professional Development" <?php echo ($event_category === "Personal & Professional Development") ? "selected" : ""; ?>>Personal & Professional Development</option>
                    </select>
                </div>
            </form>


            <h2>Choose Your Bet!</h2>
            <p class="description">
                Click on “Register Now” for more details.
            </p>

            <?php if (empty($events)) : ?>
                <div class="no-events-wrapper">
                    <p class="no-events-message">Sorry Scheduler, there are no available events at this point in time.</p>
                </div>
            <?php else : ?>
                <div class="event-panel-container">
                    <?php foreach ($events as $event) : ?>
                        <div class="event-panel">
                            <img src="<?php echo htmlspecialchars($event['event_photo']) ?>" alt="Event Photo" class="event-image">
                            <h3><?php echo htmlspecialchars($event['event_name']) ?></h3>
                            <p class="event-category"><strong>Category: </strong><?php echo htmlspecialchars($event['event_category']) ?></p>
                            <p class="event-slots"><strong>Slots: </strong><?php echo htmlspecialchars($event['event_slots']) ?></p>
                            <p class="event-description"><?php echo htmlspecialchars($event['event_description']) ?></p>
                            <div class="button-wrapper"><button class="register-button">Register Now</button></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="separator-line"></div>

            <h2>Contact Us</h2>
            <p class="description">
                Have questions or need assistance? We're here to help! Feel free to reach out to us for any inquiries about event registrations, technical support, or general concerns.
            </p>

            <div class="contact-info">
                <div class="contact-item">
                    <img src="photos/address-icon.png" alt="Address" class="contact-icon">
                    <div class="contact-text">
                        <strong>Address:</strong> 1234 Rizal Street, Makati City, Metro Manila, Philippines
                    </div>
                </div>

                <div class="contact-item">
                    <img src="photos/email-icon.png" alt="Email" class="contact-icon">
                    <div class="contact-text">
                        <strong>Email:</strong> support@scheduleevents.ph
                    </div>
                </div>

                <div class="contact-item">
                    <img src="photos/phone-icon.png" alt="Phone" class="contact-icon">
                    <div class="contact-text">
                        <strong>Phone:</strong> (+63) 912-345-6789
                    </div>
                </div>

                <div class="contact-item">
                    <img src="photos/social-icon.png" alt="Socials" class="contact-icon">
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