<?php
require_once 'DbConnection.php';
require_once 'CRUD.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$UserManager = new UserManager($conn);

if (isset($_GET['event_id'])) {
    $_SESSION['event_id'] = $_GET['event_id'];
}

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

    <!-- Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <p id="success-message"></p>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="eventRegistrationModal" class="event-registration-modal" style="display: none;">
        <div class="event-registration-modal-content">
            <span class="event-registration-close-button">×</span>
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
                <h1>Available Events</h1>
                <p>Discover upcoming events, seminars, and workshops. Browse the full list and join the ones that interests you!.</p>
            </div>

            <div class="separator-line"></div>

            <h2>Event Categories</h2>
            <p class="description">
                Personalize what you see! Choose what is best suited for your taste.
            </p>

            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search events..." class="search-input">
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

            <div class="no-events-wrapper">
                <p class="no-events-message" id="no-events-message" style="display: <?php echo empty($events) ? 'block' : 'none'; ?>;">
                    Sorry Scheduler, there are no available events at this point in time.
                </p>
            </div>

            <?php if (!empty($events)) : ?>
                <div class="event-panel-container">
                    <?php foreach ($events as $event) : ?>
                        <div class="event-panel">
                            <img src="<?php echo htmlspecialchars($event['event_photo']) ?>" alt="Event Photo" class="event-image">
                            <h3><?php echo htmlspecialchars($event['event_name']) ?></h3>
                            <p class="event-category"><strong>Category: </strong><?php echo htmlspecialchars($event['event_category']) ?></p>
                            <p class="event-slots"><strong>Slots: </strong><?php echo htmlspecialchars($event['event_slots']) ?></p>
                            <p class="event-description"><?php echo htmlspecialchars($event['event_description']) ?></p>
                            <div class="button-wrapper">
                                <button onclick="window.location.href='ViewEventForParticipant.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>&source=available-events'" class="view-button">View Event</button>
                                <button class="register-button open-registration-modal" data-event-id="<?php echo htmlspecialchars($event['event_id']); ?>">Register Now</button>
                            </div>
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
            const navItems = document.querySelectorAll('.nav-item');
            const currentPage = window.location.pathname.toLowerCase();
            console.log('Current Page:', currentPage);

            // Remove 'active' class from all navigation items
            navItems.forEach(function(item) {
                item.classList.remove('active');
            });

            navItems.forEach(function(item) {
                const href = item.getAttribute('href').toLowerCase();
                const hrefPage = href.split('/').pop().split('?')[0];
                const currentPageName = currentPage.split('/').pop().split('?')[0];

                if (hrefPage === currentPageName ||
                    (currentPageName === 'AvailableEvent.php' && item.id === 'available-events')) {
                    item.classList.add('active');
                }
            });

            // Function to show a pop-up message
            function showPopup(message, isSuccess) {
                const popup = document.createElement('div');
                popup.className = `popup-message ${isSuccess ? 'success' : 'error'}`;
                popup.textContent = message;

                document.body.appendChild(popup);

                setTimeout(() => {
                    popup.remove();
                }, 3000);
            }

            const modal = document.getElementById('eventRegistrationModal');
            const closeModalButton = document.querySelector('.event-registration-close-button');
            const registerButtons = document.querySelectorAll('.open-registration-modal');

            // Function to handle cancel registration
            function setupCancelButton() {
                const cancelButton = document.getElementById('cancel-registration-button');
                if (cancelButton) {
                    cancelButton.addEventListener('click', function() {
                        if (confirm('Are you sure you want to cancel your registration?')) {
                            const eventId = this.getAttribute('data-event-id');

                            const formData = new FormData();
                            formData.append('action', 'cancel_registration');
                            formData.append('event_id', eventId);

                            fetch('EventRegistration.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(res => res.json())
                                .then(data => {
                                    showPopup(data.message, data.success);

                                    if (data.success) {
                                        setTimeout(() => {
                                            loadModalContent(eventId);
                                        }, 1000);
                                    }
                                })
                                .catch(err => {
                                    console.error('Cancellation error:', err);
                                    showPopup('An error occurred during cancellation', false);
                                });
                        }
                    });
                }
            }

            // Function to handle form submission
            function setupRegistrationForm() {
                const form = document.getElementById('event-registration-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        const eventId = formData.get('event_id');

                        fetch('EventRegistration.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                showPopup(data.message, data.success);

                                if (data.success) {
                                    setTimeout(() => {
                                        loadModalContent(eventId);
                                    }, 1000);
                                }
                            })
                            .catch(err => {
                                console.error('Registration error:', err);
                                showPopup('An error occurred during registration', false);
                            });
                    });
                }
            }

            // Function to load modal content
            function loadModalContent(eventId) {
                fetch(`EventRegistration.php?event_id=${eventId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('modal-content').innerHTML = data;

                        setupCancelButton();
                        setupRegistrationForm();
                    })
                    .catch(error => {
                        console.error('Error loading modal content:', error);
                        showPopup('Error loading registration form', false);
                    });
            }

            // Open modal and load content
            registerButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-event-id');
                    modal.style.display = 'flex';
                    loadModalContent(eventId);
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

            // Search functionality for event name
            const searchInput = document.getElementById('search-input');
            const searchButton = document.querySelector('.search-button');
            const eventPanels = document.querySelectorAll('.event-panel');
            const noEventsMessage = document.getElementById('no-events-message');

            function filterEventsBySearch() {
                const query = searchInput.value.trim().toLowerCase();
                let hasMatches = false;

                eventPanels.forEach(panel => {
                    const eventName = panel.querySelector('h3')?.textContent.toLowerCase() || '';
                    const matches = eventName.includes(query);

                    panel.style.display = matches || query === '' ? 'block' : 'none';
                    if (matches) {
                        hasMatches = true;
                    }
                });

                // Show "no events" message if no matches and query is not empty
                noEventsMessage.style.display = (query !== '' && !hasMatches) ? 'block' : 'none';
            }

            searchButton.addEventListener('click', filterEventsBySearch);
            searchInput.addEventListener('input', filterEventsBySearch);

            // Existing fetchEvents function (unchanged)
            function fetchEvents() {
                const eventPanelsContainer = document.querySelector('.event-panel-container');
                const noEventsMessage = document.getElementById('noEventsMessage');

                fetch('MyEvents.php?action=fetch_events')
                    .then(response => response.json())
                    .then(events => {
                        eventPanelsContainer.innerHTML = '';

                        if (events.length === 0) {
                            noEventsMessage.style.display = 'block';
                            return;
                        }

                        noEventsMessage.style.display = 'none';

                        events.forEach(event => {
                            const panel = document.createElement('div');
                            panel.classList.add('event-panel');
                            panel.setAttribute('data-status', event.event_status);

                            const eventImage = document.createElement('img');
                            eventImage.src = event.event_photo;
                            eventImage.alt = 'Event Image';
                            eventImage.classList.add('event-image');

                            const eventName = document.createElement('h3');
                            eventName.classList.add('event-name');
                            eventName.textContent = event.event_name;

                            const eventCategory = document.createElement('p');
                            eventCategory.classList.add('event-category');
                            eventCategory.innerHTML = `<strong>Category: </strong>${event.event_category}`;

                            const eventSlots = document.createElement('p');
                            eventSlots.classList.add('event-slots');
                            eventSlots.innerHTML = `<strong>Slots: </strong>${event.event_slots}`;

                            const eventDescription = document.createElement('p');
                            eventDescription.classList.add('event-description');
                            eventDescription.textContent = event.event_description;

                            const buttonWrapper = document.createElement('div');
                            buttonWrapper.classList.add('button-wrapper');

                            const viewButton = document.createElement('button');
                            viewButton.classList.add('view-button');
                            viewButton.textContent = 'View Event';
                            viewButton.onclick = function() {
                                window.location.href = `ViewEvent.php?event_id=${event.event_id}`;
                            };

                            buttonWrapper.appendChild(viewButton);

                            panel.appendChild(eventImage);
                            panel.appendChild(eventName);
                            panel.appendChild(eventCategory);
                            panel.appendChild(eventSlots);
                            panel.appendChild(eventDescription);
                            panel.appendChild(buttonWrapper);

                            eventPanelsContainer.appendChild(panel);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching events:', error);
                    });
            }
        });
    </script>

</body>

</html>