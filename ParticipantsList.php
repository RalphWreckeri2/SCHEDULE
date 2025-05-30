<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

$event = null;
$error_message = null;
$success_message = null;
$participants = [];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: SignIn.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle participant deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_participant'])) {
    $participant_id = isset($_POST['participant_id']) ? $_POST['participant_id'] : '';
    $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : '';

    // Validate inputs
    if (empty($participant_id)) {
        $error_message = "Error: Missing participant ID.";
    } else if (empty($event_id)) {
        $error_message = "Error: Missing event ID.";
    } else {
        try {
            $result = $UserManager->DeleteParticipant($participant_id, $event_id);
            if ($result) {
                $success_message = "Participant removed successfully.";
                // Refresh the participants list
                $participants = $UserManager->GetEventParticipants($event_id);
            } else {
                $error_message = "Failed to remove participant. Please try again.";
            }
        } catch (Exception $e) {
            error_log("Error deleting participant: " . $e->getMessage());
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Get the specific event ID from URL parameter
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $referrer = isset($_GET['ref']) ? $_GET['ref'] : 'my-events'; // Default referrer is my-events

    // Get the specific event details
    $event = $UserManager->GetEventById($event_id);

    if (!$event) {
        $error_message = "Event not found.";
    } else if ($event['user_id'] != $user_id) {
        $error_message = "You don't have permission to view this event's participants.";
    } else {
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
            <?php if ($event): ?>
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
                    <form id="search-form" style="display: flex; width: 100%; gap: 10px;">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($referrer); ?>">
                        <input type="text" id="search-input" placeholder="Search participants..." class="participants-search-input">
                        <button type="submit" id="search-button" class="participants-search-button">Search</button>
                    </form>
                </div>

                <div class="participants-list-container">
                    <p class="no-participants" id="no-participants-message" style="display: <?php echo empty($participants) ? 'block' : 'none'; ?>;">
                        No participants registered for this event yet.
                    </p>
                    <?php if (!empty($participants)): ?>
                        <div class="participants-list">
                            <div class="participants-list-labels">
                                <p class="label">Name</p>
                                <p class="label">Email</p>
                                <p class="label">Phone</p>
                                <p class="label">Actions</p>
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
                    <div class="export-button-container">
                        <a href="ExportParticipants.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>" class="export-button">
                            Export
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            const currentPage = window.location.pathname;

            navItems.forEach(function(item) {
                item.classList.remove('active');
            });

            navItems.forEach(function(item) {
                const href = item.getAttribute('href');
                const hrefPage = href.split('/').pop();
                const currentPageName = currentPage.split('/').pop();

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

            // Auto-hide success message after 3 seconds
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000);
            }

            // Real-time search functionality
            const searchForm = document.getElementById('search-form');
            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');
            const participantItems = document.querySelectorAll('.participant-item');
            const noParticipantsMessage = document.getElementById('no-participants-message');

            // Prevent form submission to avoid page reload
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterParticipants();
            });

            function filterParticipants() {
                const query = searchInput.value.trim().toLowerCase();
                let hasMatches = false;

                participantItems.forEach(item => {
                    const name = item.querySelector('.participant-name')?.textContent.toLowerCase() || '';
                    const email = item.querySelector('.participant-email')?.textContent.toLowerCase() || '';
                    const matches = name.includes(query) || email.includes(query);

                    item.style.display = matches || query === '' ? 'grid' : 'none';
                    if (matches) {
                        hasMatches = true;
                    }
                });

                // Show "no participants" message if no matches and query is not empty
                noParticipantsMessage.style.display = (query !== '' && !hasMatches) ? 'block' : 'none';
            }

            // Bind search to input event for real-time filtering and button click
            searchInput.addEventListener('input', filterParticipants);
            searchButton.addEventListener('click', filterParticipants);

            // Function to confirm deletion
            function confirmDelete() {
                return confirm('Are you sure you want to remove this participant? This action cannot be undone.');
            }
        });

        // Show alert for success message
        <?php if ($success_message): ?>
            alert("<?php echo $success_message; ?>");
        <?php endif; ?>
    </script>

</body>

</html>