<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

$event = null;
$error_message = null;
$participants = [];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the specific event ID from URL parameter
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $referrer = isset($_GET['ref']) ? $_GET['ref'] : 'my-events'; // Default referrer is my-events

    // Get the specific event details
    $event = $UserManager->GetEventById($event_id);

    if (!$event) {
        $error_message = "Event not found.";
    } else if ($event['user_id'] != $user_id) {
        // Make sure the event belongs to the current user
        $error_message = "You don't have permission to view this event's participants.";
    } else {
        // Get participants for this event
        try {
            $participants = $UserManager->GetEventParticipants($event_id);
            // For debugging
            error_log("Participants count: " . count($participants));
            error_log("Participants data: " . print_r($participants, true));
        } catch (Exception $e) {
            error_log("Error getting participants: " . $e->getMessage());
            $error_message = "Error retrieving participants: " . $e->getMessage();
            $participants = []; // Set to empty array if there's an error
        }
    }
} else {
    $error_message = "No event specified.";
}

// Handle search functionality
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_query) && !empty($participants)) {
    $filtered_participants = [];
    foreach ($participants as $participant) {
        if (
            stripos($participant['name'], $search_query) !== false ||
            stripos($participant['email'], $search_query) !== false
        ) {
            $filtered_participants[] = $participant;
        }
    }
    $participants = $filtered_participants;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants List - Schedule Events</title>
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
    <div class="participants-main-content">
        <div class="participants-in-main-content">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <h2>Error</h2>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <!--HINDI KO MAAYOS<a href="MyEvents.php" class="back-button">Back to My Events</a>-->
                </div>
            <?php elseif ($event): ?>
                <div class="header">
                    <h1>Participants List</h1>
                    <p>Manage and track event participants effectively.</p>
                </div>

                <div class="separator-line"></div>

                <div class="event-info">
                    <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($event['event_date']))); ?></p>
                    <p><strong>Slots:</strong> <?php echo htmlspecialchars($event['event_slots']); ?> (<?php echo count($participants); ?> taken)</p>
                </div>

                <div class="participants-search-bar">
                    <form method="GET" action="ParticipantsList.php" style="display: flex; width: 100%; gap: 10px;">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($referrer); ?>">
                        <input type="text" name="search" placeholder="Search participants..." class="participants-search-input" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="participants-search-button">Search</button>
                    </form>
                </div>

                <div class="participants-list-container">
                    <?php if (empty($participants)): ?>
                        <p class="no-participants">No participants registered for this event yet.</p>
                    <?php else: ?>
                        <div class="participants-list">
                            <div class="participants-list-labels">
                                <p class="label">Name</p>
                                <p class="label">Email</p>
                                <p class="label">Phone</p>
                            </div>

                            <?php foreach ($participants as $participant): ?>
                                <div class="participant-item">
                                    <p class="participant-name"><?php echo htmlspecialchars($participant['name'] ?? 'N/A'); ?></p>
                                    <p class="participant-email"><?php echo htmlspecialchars($participant['email'] ?? 'N/A'); ?></p>
                                    <p class="participant-phone"><?php echo htmlspecialchars($participant['phone'] ?? 'N/A'); ?></p>
                                    <div class="participant-actions">
                                        <form method="post">
                                            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                            <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($participant['id'] ?? ''); ?>">
                                            <button type="submit" name="delete_participant" class="delete-participant-button" onclick="return confirm('Are you sure you want to remove this participant?');">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                    (currentPageName === 'ViewEvent.php' && item.id === 'my-events') ||
                    (currentPageName === 'ParticipantsList.php' && item.id === 'my-events')
                ) {
                    item.classList.add('active');
                }
            });
        });
    </script>

</body>

</html>