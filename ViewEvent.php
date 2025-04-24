<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

$event = null;
$error_message = null;
$success_message = null;
$participants = [];
$referrer = isset($_GET['ref']) ? $_GET['ref'] : 'my-events'; // Default referrer is my-events

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the specific event ID from URL parameter
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Get the specific event details
    $event = $UserManager->GetEventById($event_id);

    if (!$event) {
        $error_message = "Event not found.";
    } else if ($event['user_id'] != $user_id) {
        // Make sure the event belongs to the current user
        $error_message = "You don't have permission to view this event.";
    } else {
        // Automatically update the status based on date
        $UserManager->UpdateEventStatusAutomatically($event_id);

        // Refresh event data to get the updated status
        $event = $UserManager->GetEventById($event_id);

        // Get participants for this event
        try {
            $participants = $UserManager->GetEventParticipants($event_id);
        } catch (Exception $e) {
            error_log("Error getting participants: " . $e->getMessage());
            $participants = []; // Set to empty array if there's an error
        }
    }
} else {
    $error_message = "No event specified.";
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];

    // Handle delete event
    if (isset($_POST['delete_event'])) {
        $result = $UserManager->DeleteEvent($event_id);
        if ($result) {
            // Redirect to MyEvents.php after successful deletion
            header("Location: MyEvents.php?deleted=true");
            exit;
        } else {
            $error_message = "Failed to delete event.";
        }
    }
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
            <?php if ($success_message): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <h2>Error</h2>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <a href="MyEvents.php" class="back-button">Back to My Events</a>
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
                            <p><strong>Slots: </strong><?php echo htmlspecialchars($event['event_slots']); ?> (<?php echo isset($event['taken_slots']) ? htmlspecialchars($event['taken_slots']) : '0'; ?> taken)</p>
                            <div class="status-display">
                                <strong>Status: </strong>
                                <span class="event-status <?php echo strtolower($event['event_status']); ?>">
                                    <?php echo htmlspecialchars($event['event_status']); ?>
                                </span>
                                <span class="status-info">(Automatically updated based on event date)</span>
                            </div>
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
                <div class="view-event-button-container">
                     <a href="<?php echo $referrer === 'dashboard' ? 'Dashboard.php' : 'MyEvents.php'; ?>" class="confirm-event-button">Back</a>
                     
                     <form method="post" id="deleteForm" onsubmit="return confirmDelete()">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                        <button type="submit" name="delete_event" class="delete-event-button">Delete</button>
                    </form>

                    <a href="EditEvent.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>&ref=<?php echo $referrer; ?>" class="edit-event-button">Edit</a>

                    <a href="ParticipantsList.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>&ref=<?php echo $referrer; ?>" class="participant-event-button">Participants (<?php echo count($participants); ?>)</a>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all navigation items
            const navItems = document.querySelectorAll('.nav-item');

            // Get current page URL and referrer
            const currentPage = window.location.pathname;
            const urlParams = new URLSearchParams(window.location.search);
            const referrer = urlParams.get('ref') || 'my-events'; // Default to my-events if not specified

            // Remove 'active' class from all navigation items
            navItems.forEach(function(item) {
                item.classList.remove('active');
            });

            // Set active based on referrer for ViewEvent.php
            if (currentPage.includes('ViewEvent.php')) {
                if (referrer === 'dashboard') {
                    document.getElementById('dashboard').classList.add('active');
                } else {
                    document.getElementById('my-events').classList.add('active');
                }
            } else {
                // For other pages, use the regular logic
                navItems.forEach(function(item) {
                    const href = item.getAttribute('href');
                    const hrefPage = href.split('/').pop();
                    const currentPageName = currentPage.split('/').pop();

                    if (currentPageName === hrefPage ||
                        (currentPageName === 'Dashboard.php' && item.id === 'dashboard') ||
                        (currentPageName === '' && item.id === 'dashboard')) {
                        item.classList.add('active');
                    }
                });
            }

            // Auto-hide success message after 3 seconds
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000);
            }
        });

        // Function to confirm deletion
        function confirmDelete() {
            return confirm('Are you sure you want to delete this event? This action cannot be undone.');
        }
    </script>

</body>

</html>