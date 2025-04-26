<?php
require_once  'DbConnection.php';
require_once  'CRUD.php';
require_once 'EventRegistration.php';

$UserManager = new UserManager($conn);

// Check if a session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if there's a success message in the session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Remove the message from the session
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $events = $UserManager->EventFetcherInDb($user_id);

      // Get events joined by the user, grouped by status
      $upcomingEvents = $UserManager->GetUserEventsByStatus($user_id, 'upcoming');
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

    <!-- Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="success-message"></p>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="eventRegistrationModal" class="event-registration-modal" style="display: none;">
        <div class="event-registration-modal-content">
            <span class="event-registration-close-button">&times;</span>
            <div id="modal-content"></div> <!-- Content will be loaded here -->
        </div>
    </div>

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
                <h1>Welcome to Schedule Events</h1>
                <p>Your one-stop solution for managing and scheduling events.</p>
            </div>

            <div class="separator-line"></div>

            <h2>Explore Featured Events</h2>
            <p class="description">
                Discover a variety of events happening around you. Join us to learn, network, and grow!
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
                            <div class="button-wrapper">
                                <button onclick="window.location.href='ViewEventForParticipant.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>&source=dashboard'" class="view-button">View Event</button>
                                <button class="register-button open-registration-modal" data-event-id="<?php echo htmlspecialchars($event['event_id']); ?>">Register Now</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="view-more-button-container">
                <img src="photos/view-more-icon.png" alt="More" class="view-more-icon">
                <a href="AvailableEvent.php" class="view-more-button">View More Events</a>
            </div>
            <!--<div class="separator-line"></div>-->

            <h2>Your Upcoming Events</h2>
            <p class="description">
                Explore new learning opportunities and gain valuable skills!
            </p>

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
                    <img src="photos/de-guzman.png" alt="Developer 1" class="developer-image">
                    <p class="developer-name">James Patrick S. De Guzman</p>
                    <p class="developer-description">Group Leader</p>
                </div>
                <div class="developer-info">
                    <img src="photos/samonte.png" alt="Developer 2" class="developer-image">
                    <p class="developer-name">Ralph Matthew A. Samonte</p>
                    <p class="developer-description">Assistant Leader</p>
                </div>
                <div class="developer-info">
                    <img src="photos/gonito.png" alt="Developer 3" class="developer-image">
                    <p class="developer-name">Drred Klain M, Gonito</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="photos/cortiguerra.png" alt="Developer 4" class="developer-image">
                    <p class="developer-name">Vinz Emmanuel D. Cortiguerra</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="photos/montes.png" alt="Developer 5" class="developer-image">
                    <p class="developer-name">Kenneth E. Montes</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="photos/protestante.png" alt="Developer 6" class="developer-image">
                    <p class="developer-name">Louisa Victoria C. Protestante</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="photos/tucay.png" alt="Developer 7" class="developer-image">
                    <p class="developer-name">Alexander James L. Tucay</p>
                    <p class="developer-description">Group Member</p>
                </div>
                <div class="developer-info">
                    <img src="photos/macaraig.png" alt="Developer 8" class="developer-image">
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

        const modal = document.getElementById('eventRegistrationModal');
        const closeModalButton = document.querySelector('.event-registration-close-button');
        const registerButtons = document.querySelectorAll('.open-registration-modal');

        // Open modal and load content
        registerButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');

                // Fetch modal content via AJAX
                fetch(`EventRegistration.php?event_id=${eventId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('modal-content').innerHTML = data;
                        modal.style.display = 'flex';

                        // Add event listener for form submission
                        const form = document.querySelector('#event-registration-form');
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault(); // Prevent default form submission

                                const formData = new FormData(form);

                                // Submit form via AJAX
                                fetch('EventRegistration.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        // Show success or error message in a modal
                                        showModal(data.message, data.success);
                                    })
                                    .catch(error => console.error('Error:', error));
                            });
                        }
                    })
                    .catch(error => console.error('Error loading modal content:', error));
            });
        });

        // Close modal
        closeModalButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside the modal content
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        //for modal(popup) after a successful sign in
        function showModal(message) {
            var modal = document.createElement('div');
            modal.classList.add('modal');
            modal.innerHTML = `
                    <div class="modal-content">
                        <span class="close-btn">&times;</span>
                        <p>${message}</p>
                    </div>
                `;
            document.body.appendChild(modal);

            // Close the modal when clicking on the close button
            modal.querySelector('.close-btn').addEventListener('click', function() {
                modal.style.display = 'none';
            });

            // Close the modal if the user clicks outside of the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // Show the modal
            modal.style.display = 'block';
        }

        // Show modal if success message is set
        <?php if (isset($success_message)): ?>
            showModal("<?php echo addslashes($success_message); ?>");
        <?php endif; ?>
    </script>

</body>

</html>