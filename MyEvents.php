<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $events = $UserManager->GetEvents($user_id);

    // Get events joined by the user, grouped by status
    $upcomingEvents = $UserManager->GetUserEventsByStatus($user_id, 'upcoming');
    $pastEvents = $UserManager->GetUserEventsByStatus($user_id, 'past');
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
                <h1>My Events</h1>
                <p>Effortlessly manage and track all your created events in one place.</p>
            </div>

            <div class="separator-line"></div>

            <h2>Events You Created</h2>
            <p class="description">
                Browse and manage your events with ease—filter them by status!
            </p>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search an event name..." class="search-input">
                <!--<button class="search-button" onclick="applySearch()">Search</button>-->
            </div>

            <div class="filter-container">
                <select id="filter" class="filter-select" onchange="applyFilter()">
                    <option value="all">All</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="current">Current</option>
                    <option value="past">Past</option>
                </select>
            </div>


            <?php if (empty($events)) : ?>
                <div class="no-events-wrapper">
                    <a href="NewEvent.php" class="no-events-message">No events created yet. Start by creating your first event!</a>
                </div>
            <?php else : ?>
                <div class="event-panel-container">
                    <?php foreach ($events as $event) : ?>
                        <div class="event-panel" data-status="<?php echo htmlspecialchars($event['event_status']); ?>">
                            <img src="<?php echo htmlspecialchars($event['event_photo']); ?>" alt="Event Image" class="event-image">
                            <h3 class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <p class="event-category"><strong>Category: </strong><?php echo htmlspecialchars($event['event_category']); ?></p>
                            <p class="event-slots"><strong>Slots: </strong><?php echo htmlspecialchars($event['event_slots']); ?></p>
                            <p class="event-description"><?php echo htmlspecialchars($event['event_description']); ?></p>
                            <div class="button-wrapper">
                                <button onclick="window.location.href='ViewEvent.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>'" class="view-button">
                                    View Event
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div id="noFilteredEventsMessage" class="no-events-wrapper" style="display: none;">
                        <p class="no-events-message">No events match the selected filter.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="separator-line"></div>

            <h2>Events You Joined</h2>
            <p class="description">Browse and manage your events with ease—segregated by status!</p>

            <h3 class="description-heading">Your Upcoming Events</h3>
            <p class="description">Explore new learning opportunities and gain valuable skills!</p>

            <div class="upcoming-events-container">
                <div class="upcoming-events-labels">
                    <div class="label">Event Name</div>
                    <div class="label">Event Date</div>
                    <div class="label">Actions</div>
                </div>
                <?php if (!empty($upcomingEvents)) : ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="event-row-grid">
                            <div><?php echo htmlspecialchars($event['event_name']); ?></div>
                            <div><?php echo htmlspecialchars($event['event_date']); ?></div>
                            <button onclick="window.location.href='ViewEventForParticipant.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>'" class="view-button-me">View</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-events-message">You have no upcoming events yet, Scheduler!.</p>
                <?php endif; ?>
            </div>

            <h3 class="description-heading">Your Past Events</h3>
            <p class="description">Rekindle the details of your past events!</p>

            <div class="past-events-container">
                <div class="past-events-labels">
                    <div class="label">Event Name</div>
                    <div class="label">Event Date</div>
                    <div class="label">Actions</div>
                </div>
                <?php if (!empty($pastEvents)) : ?>
                    <?php foreach ($pastEvents as $event): ?>
                        <div class="event-row-grid">
                            <div><?php echo htmlspecialchars($event['event_name']); ?></div>
                            <div><?php echo htmlspecialchars($event['event_date']); ?></div>
                            <button onclick="window.location.href='ViewEventForParticipant.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>'" class="view-button-me">View</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-events-message">You have no past events yet, Scheduler!</p>
                <?php endif; ?>
            </div>

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
            const navItems = document.querySelectorAll('.nav-item');
            const currentPage = window.location.pathname;

            // Remove 'active' class from all navigation items
            navItems.forEach(function(item) {
                item.classList.remove('active');
            });

            // Find which nav item matches the current page and set it as active
            navItems.forEach(function(item) {
                const href = item.getAttribute('href');
                const hrefPage = href.split('/').pop(); // Extract just the filename from the href           
                const currentPageName = currentPage.split('/').pop(); // Extract just the filename from the current URL

                // Check if this nav item corresponds to the current page and set it as active
                if (currentPageName === hrefPage ||
                    (currentPageName === 'Dashboard.php' && item.id === 'dashboard') ||
                    (currentPageName === '' && item.id === 'dashboard')) {
                    item.classList.add('active');
                    console.log('Set active:', item.id);
                }
            });

            // Function to filter events based on the status selected
            function applyFilter() {
                const filterValue = document.getElementById('filter').value;
                const eventPanels = document.querySelectorAll('.event-panel');
                const noFilteredMessage = document.getElementById('noFilteredEventsMessage');

                let visibleCount = 0;

                eventPanels.forEach(function(event) {
                    const eventStatus = event.getAttribute('data-status');

                    if (filterValue === 'all' || eventStatus === filterValue) {
                        event.style.display = 'block';
                        visibleCount++;
                    } else {
                        event.style.display = 'none';
                    }
                });

                // Show the "no events" message if none are visible
                if (visibleCount === 0) {
                    noFilteredMessage.style.display = 'block';
                } else {
                    noFilteredMessage.style.display = 'none';
                }
            }


            // Function to search events based on user input
            function applySearch() {
                const searchQuery = document.getElementById('searchInput').value.toLowerCase();
                const eventPanels = document.querySelectorAll('.event-panel');

                eventPanels.forEach(panel => {
                    const eventName = panel.querySelector('.event-name').textContent.toLowerCase();
                    if (eventName.includes(searchQuery)) {
                        panel.style.display = 'block';
                    } else {
                        panel.style.display = 'none';
                    }
                });
            }

            // Add event listeners to trigger search and filter automatically
            document.getElementById('searchInput').addEventListener('keyup', applySearch);
            document.getElementById('filter').addEventListener('change', applyFilter);

            // Run the filter on page load in case a filter was selected
            applyFilter();
        });
    </script>


</body>

</html>