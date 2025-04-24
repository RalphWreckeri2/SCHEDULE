<?php
include 'DbConnection.php';
include 'CRUD.php';

$referrer = isset($_GET['source']) ? $_GET['source'] : null;
$UserManager = new UserManager($conn);
session_start();

$event = null;
$error_message = null;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the specific event ID from URL parameter
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $_SESSION['event_id'] = $event_id; // Store in session if needed

    // Get the specific event details
    $event = $UserManager->GetEventById($event_id);

    if (!$event) {
        $error_message = "Event not found.";
    }
} else {
    $error_message = "No event specified.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details - Schedule Events</title>
    <link rel="stylesheet" href="styles1.css">
</head>

<body>

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
    <div class="view-event-main-content">
        <div class="view-event-in-main-content">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <h2>Error</h2>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <a href="AvailableEvent.php" class="back-button">Back to Available Events</a>
                </div>
            <?php elseif ($event): ?>
                <div class="header">
                    <h1>Event Details</h1>
                    <div class="separator"></div>
                </div>

                <div class="event-wrapper">
                    <div class="event-top-section">

                        <!-- LEFT: Event Photo -->
                        <div class="event-photo-container">
                            <img src="<?php echo !empty($event['event_photo']) ? htmlspecialchars($event['event_photo']) : 'photos/event-default.jpg'; ?>" alt="Event Photo" class="event-photo">
                        </div>

                        <!-- RIGHT: Event Details -->
                        <div class="event-details-container">
                            <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
                            <p><strong>Category: </strong><?php echo htmlspecialchars($event['event_category']); ?></p>
                            <p><strong>Slots: </strong><?php echo htmlspecialchars($event['event_slots']); ?></p>
                            <p><strong>Status: </strong><?php echo htmlspecialchars($event['event_status']); ?></p>
                            <p><strong>Description: </strong><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
                            <p><strong>Date: </strong><?php echo htmlspecialchars(date('F j, Y', strtotime($event['event_date']))); ?></p>
                            <p><strong>Time: </strong><?php echo htmlspecialchars(date('g:i A', strtotime($event['event_starting_time']))); ?> - <?php echo htmlspecialchars(date('g:i A', strtotime($event['event_end_time']))); ?></p>
                            <p><strong>Location: </strong><?php echo htmlspecialchars($event['event_location']); ?></p>
                            <?php if (!empty($event['event_speaker'])): ?>
                                <p><strong>Speaker: </strong><?php echo htmlspecialchars($event['event_speaker']); ?></p>
                                <?php if (!empty($event['speaker_description'])): ?>
                                    <p><strong>Speaker Bio: </strong><?php echo nl2br(htmlspecialchars($event['speaker_description'])); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- BOTTOM: Buttons -->
                <div class="button-wrapper">
                    <button class="register-button open-registration-modal" data-event-id="<?php echo htmlspecialchars($event['event_id']); ?>">Register Now</button>
                </div>
            <?php endif; ?>
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

            const urlParams = new URLSearchParams(window.location.search);
            const source = urlParams.get('source');

            // Add the 'active' class to the correct nav item based on the source
            if (source === 'dashboard') {
                document.getElementById('dashboard').classList.add('active');
            } else if (source === 'available-events') {
                document.getElementById('available-events').classList.add('active');
            } else {
                // Handle other sources or default behavior
                document.getElementById('my-events').classList.add('active');
            }

            // Function to show a pop-up message
            function showPopup(message, isSuccess) {
                const popup = document.createElement('div');
                popup.className = `popup-message ${isSuccess ? 'success' : 'error'}`;
                popup.textContent = message;

                document.body.appendChild(popup);

                // Remove the pop-up after 3 seconds
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
                                    // Show the pop-up message
                                    showPopup(data.message, data.success);

                                    if (data.success) {
                                        // Reload the modal content to show registration form again
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
                                // Show the pop-up message
                                showPopup(data.message, data.success);

                                if (data.success) {
                                    // Reload the modal content to show cancel button
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
                console.log('Loading modal content for event ID:', eventId);

                fetch(`EventRegistration.php?event_id=${eventId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(data => {
                        document.getElementById('modal-content').innerHTML = data;

                        // Setup event handlers for the new content
                        setupCancelButton();
                        setupRegistrationForm();
                    })
                    .catch(error => {
                        console.error('Error loading modal content:', error);
                        document.getElementById('modal-content').innerHTML = `
                            <div class="error-message">
                                <h3>Error Loading Registration Form</h3>
                                <p>There was a problem loading the registration form. Please try again later.</p>
                                <p>Technical details: ${error.message}</p>
                            </div>
                        `;
                    });
            }

            // Open modal and load content
            if (registerButtons.length > 0) {
                registerButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const eventId = this.getAttribute('data-event-id');
                        if (!eventId) {
                            console.error('No event ID found on button');
                            showPopup('Error: No event ID found', false);
                            return;
                        }

                        modal.style.display = 'flex';
                        loadModalContent(eventId);
                    });
                });
            }

            // Close modal
            if (closeModalButton) {
                closeModalButton.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }

            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>