<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);
session_start();

$event = null;
$referrer = isset($_GET['ref']) ? $_GET['ref'] : 'my-events'; // Default referrer is my-events
$error_message = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: SignIn.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    $event = $UserManager->GetEventById($event_id);

    if (!$event) {
        $error_message = "Event not found.";
    } else if ($event['user_id'] != $user_id) {
        $error_message = "You don't have permission to edit this event.";
    }
} else {
    $error_message = "No event specified.";
}

// Initialize form data with current event values
$formData = $event ? [
    'event_name' => $event['event_name'],
    'event_category' => $event['event_category'],
    'event_slots' => $event['event_slots'],
    'event_description' => $event['event_description'],
    'event_date' => $event['event_date'],
    'event_starting_time' => $event['event_starting_time'],
    'event_end_time' => $event['event_end_time'],
    'event_location' => $event['event_location'],
    'event_speaker' => '',
    'speaker_description' => ''
] : [];

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['update_event'])) {
    $event_id = $_POST['event_id'];

    // Store form data for repopulation in case of validation errors
    $formData = [
        'event_name' => $_POST['event-name'],
        'event_category' => $_POST['event-category'],
        'event_slots' => $_POST['event-slots'],
        'event_description' => $_POST['event-description'],
        'event_date' => $_POST['event-date'],
        'event_starting_time' => $_POST['event-starting-time'],
        'event_end-time' => $_POST['event-end-time'],
        'event_location' => $_POST['event-location'],
        'event_speaker' => $_POST['event-speaker'],
        'speaker_description' => $_POST['speaker-description']
    ];

    // Validate required fields
    $required_fields = [
        'event-name' => 'Event Name',
        'event-category' => 'Event Category',
        'event-slots' => 'Total Slots',
        'event-description' => 'Event Description',
        'event-date' => 'Event Date',
        'event-starting-time' => 'Event Starting Time',
        'event-end-time' => 'Event End Time',
        'event-location' => 'Event Location',
        'event-speaker' => 'Event Speaker',
        'speaker-description' => 'Speaker Description'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            $error_message = $label . " is required.";
            break;
        }
    }

    // Validate event slots
    if (empty($error_message)) {
        $eventSlots = (int)$_POST['event-slots'];
        if (!is_numeric($eventSlots) || $eventSlots < 1) {
            $error_message = "Number of slots must be at least 1.";
        }
    }

    // Validate event time
    if (empty($error_message)) {
        $eventStartingTime = $_POST['event-starting-time'];
        $eventEndTime = $_POST['event-end-time'];

        if ($eventStartingTime >= $eventEndTime) {
            $error_message = "Event end time must be after start time.";
        }
    }

    if (empty($error_message)) {
        // Validate event date
        $eventDateObj = new DateTime($_POST['event-date']);
        $today = new DateTime();
        $today->setTime(0, 0);
        $eventDateObj->setTime(0, 0);

        if ($eventDateObj < $today) {
            $error_message = "Event date cannot be in the past.";
        } else {
            // For existing events, we might want to be more flexible with the 2-week rule
            // Only apply the 2-week rule for events that are still "upcoming"
            $interval = $today->diff($eventDateObj)->days;
            if ($interval < 14 && $event['event_status'] == 'upcoming') {
                $error_message = "Event must be scheduled at least 2 weeks from today.";
            }
        }
    }

    // File upload handling
    $eventPhoto = $event['event_photo']; // Default to current photo

    if (empty($error_message) && isset($_FILES["event-photo"]) && $_FILES["event-photo"]["error"] == UPLOAD_ERR_OK && $_FILES["event-photo"]["size"] > 0) {
        $targetDir = "uploads/";
        $imageName = basename($_FILES["event-photo"]["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["event-photo"]["tmp_name"], $targetFile)) {
            $eventPhoto = $targetFile;
        } else {
            $error_message = "Error uploading image.";
        }
    }

    if (empty($error_message)) {
        // Get form data with validation
        $eventName = $_POST['event-name'];
        $eventCategory = $_POST['event-category'];
        $eventSlots = (int)$_POST['event-slots'];
        $eventDescription = $_POST['event-description'];
        $eventDate = $_POST['event-date'];
        $eventStartingTime = $_POST['event-starting-time'];
        $eventEndTime = $_POST['event-end-time'];
        $eventLocation = $_POST['event-location'];

        // Fix category values to match database enum
        $categoryMap = [
            'business-and-finance' => 'Business & Finance',
            'technology-and-innovation' => 'Technology & Innovation',
            'health-and-wellness' => 'Health & Wellness',
            'personal-and-professional-development' => 'Personal & Professional Development'
        ];

        if (isset($categoryMap[$eventCategory])) {
            $eventCategory = $categoryMap[$eventCategory];
        }

        // Get the main speaker data
        $eventSpeaker = $_POST['event-speaker'];
        $speakerDescription = $_POST['speaker-description'];

        // Initialize with the main speaker
        $allSpeakers = $eventSpeaker;
        $allDescriptions = $speakerDescription;

        // Handle additional speakers (check if they exist and are not empty)
        if (isset($_POST['additional-speaker']) && is_array($_POST['additional-speaker'])) {
            for ($i = 0; $i < count($_POST['additional-speaker']); $i++) {
                if (!empty($_POST['additional-speaker'][$i]) && !empty($_POST['additional-description'][$i])) {
                    $allSpeakers .= " || " . $_POST['additional-speaker'][$i];
                    $allDescriptions .= " || " . $_POST['additional-description'][$i];
                }
            }
        }

        // Update the event
        $result = $UserManager->UpdateEvent(
            $event_id,
            $user_id,
            $eventPhoto,
            $eventName,
            $eventCategory,
            $eventSlots,
            null, // Status is now automatically determined
            $eventDescription,
            $eventDate,
            $eventStartingTime,
            $eventEndTime,
            $eventLocation,
            $allSpeakers,
            $allDescriptions
        );

        if ($result['success']) {
            // Automatically update the status based on the new date
            $UserManager->UpdateEventStatusAutomatically($event_id);

            echo "<script>
                alert('Event updated successfully!');
                window.location.href = 'MyEvents.php';
            </script>";
            exit; // Para hindi pa niya ituloy yung ibang PHP code after redirect
        } else {
            $error_message = isset($result['error']) ? $result['error'] : 'Failed to update event.';
        }
    }
}

// Parse speakers and descriptions if they contain delimiters
$speakers = [];
$descriptions = [];

if ($event && !empty($event['event_speaker'])) {
    $speakers = explode(" || ", $event['event_speaker']);
    $descriptions = explode(" || ", $event['speaker_description']);
}

// Ensure we have at least one speaker
if (empty($speakers)) {
    $speakers = [''];
    $descriptions = [''];
}

// Set the main speaker and description
$mainSpeaker = $speakers[0] ?? '';
$mainDescription = $descriptions[0] ?? '';

// Remove the main speaker from the arrays to avoid duplication
if (count($speakers) > 0) {
    array_shift($speakers);
    array_shift($descriptions);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Schedule Events</title>
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

            <?php if ($event): ?>
                <div class="header">
                    <h1>Edit Event</h1>
                    <p>Update the following details to edit your event.</p>
                </div>

                <div class="separator-line"></div>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="EditEvent.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>&ref=<?php echo $referrer; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">

                    <div class="form-container">
                        <div class="form-field">
                            <label for="event-photo">Current event cover photo:</label>
                            <img src="<?php echo htmlspecialchars($event['event_photo']); ?>" alt="Current Event Photo" style="max-width: 200px; margin-bottom: 10px;">
                            <label for="event-photo">Upload new photo (optional):</label>
                            <input type="file" name="event-photo" accept=".jpeg, .jpg, .png">
                        </div>
                        <div class="form-field">
                            <label for="event-name">Event Name:</label>
                            <input type="text" name="event-name" placeholder="Event Name" value="<?php echo htmlspecialchars($formData['event_name'] ?? $event['event_name']); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-row-field">
                                <label for="event-category">Event Category:</label>
                                <select name="event-category" required>
                                    <?php
                                    $categories = [
                                        'business-and-finance' => 'Business & Finance',
                                        'technology-and-innovation' => 'Technology & Innovation',
                                        'health-and-wellness' => 'Health & Wellness',
                                        'personal-and-professional-development' => 'Personal & Professional Development'
                                    ];

                                    $selectedCategory = $formData['event_category'] ?? $event['event_category'];

                                    foreach ($categories as $value => $label) {
                                        $selected = ($selectedCategory == $label) ? 'selected' : '';
                                        echo "<option value=\"$value\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-row-field">
                                <label for="event-slots">Total Slots:</label>
                                <input type="number" name="event-slots" placeholder="Total Slots" value="<?php echo htmlspecialchars($formData['event_slots'] ?? $event['event_slots']); ?>" min="1" required>
                            </div>
                            <div class="form-row-field">
                                <label for="event-status">Event Status:</label>
                                <div class="status-display">
                                    <span class="event-status <?php echo strtolower($event['event_status']); ?>">
                                        <?php echo htmlspecialchars($event['event_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="event-description">Event Description:</label>
                            <textarea name="event-description" placeholder="Event Description" required><?php echo htmlspecialchars($formData['event_description'] ?? $event['event_description']); ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-row-field">
                                <label for="event-date">Event Date:</label>
                                <input type="date" name="event-date" value="<?php echo htmlspecialchars($formData['event_date'] ?? $event['event_date']); ?>" required>
                            </div>
                            <div class="form-row-field">
                                <label for="event-starting-time">Event Starting Time:</label>
                                <input type="time" name="event-starting-time" value="<?php echo htmlspecialchars($formData['event_starting_time'] ?? $event['event_starting_time']); ?>" required>
                            </div>
                            <div class="form-row-field">
                                <label for="event-end-time">Event End Time:</label>
                                <input type="time" name="event-end-time" value="<?php echo htmlspecialchars($formData['event_end_time'] ?? $event['event_end_time']); ?>" required>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="event-location">Event Location:</label>
                            <input type="text" name="event-location" placeholder="Event Location" value="<?php echo htmlspecialchars($formData['event_location'] ?? $event['event_location']); ?>" required>
                        </div>
                        <div class="form-row-field2">
                            <div class="form-field">
                                <label for="event-speaker">Event Speaker:</label>
                                <input type="text" name="event-speaker" placeholder="Add Speaker" value="<?php echo htmlspecialchars($mainSpeaker); ?>" required>
                            </div>
                            <div class="form-field">
                                <label for="speaker-description">Description:</label>
                                <input type="text" name="speaker-description" placeholder="Speaker Description" value="<?php echo htmlspecialchars($mainDescription); ?>" required>
                            </div>
                            <div class="form-field">
                                <div id="additional-speakers">
                                    <?php
                                    // Display additional speakers if any
                                    for ($i = 0; $i < count($speakers); $i++) {
                                        echo '<div class="additional-speaker form-row-field2">';
                                        echo '<div class="form-field">';
                                        echo '<label for="additional-speaker-' . $i . '">Additional Speaker:</label>';
                                        echo '<input type="text" name="additional-speaker[]" id="additional-speaker-' . $i . '" placeholder="Add Speaker" value="' . htmlspecialchars($speakers[$i]) . '">';
                                        echo '</div>';
                                        echo '<div class="form-field">';
                                        echo '<label for="additional-description-' . $i . '">Description:</label>';
                                        echo '<input type="text" name="additional-description[]" id="additional-description-' . $i . '" placeholder="Speaker Description" value="' . htmlspecialchars($descriptions[$i]) . '">';
                                        echo '</div>';
                                        echo '<div class="form-field">';
                                        echo '<button type="button" class="remove-speaker-button">Remove</button>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="add-speaker-button-container">
                            <button type="button" class="add-speaker-button" id="add-speaker-button">Add Speaker</button>
                        </div>
                        <div class="button-row">
                            <a href="ViewEvent.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>&ref=<?php echo $referrer; ?>" class="cancel-button">Cancel</a>
                            <button type="submit" name="update_event" class="submit-button">Update</button>
                        </div>
                    </div>
                </form>

            <?php else: ?>
                <div class="error-message">
                    <h2>Event Not Found</h2>
                    <p>The event you are trying to edit doesn't exist.</p>
                    <a href="MyEvents.php" class="back-button">Back to My Events</a>
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

            // Get current page URL and referrer
            const currentPage = window.location.pathname;
            const urlParams = new URLSearchParams(window.location.search);
            const referrer = urlParams.get('ref') || 'my-events'; // Default to my-events if not specified

            // Remove 'active' class from all navigation items
            navItems.forEach(function(item) {
                item.classList.remove('active');
            });

            // Set active based on referrer for EditEvent.php
            if (currentPage.includes('EditEvent.php')) {
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

            // Add form validation on submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                // Validate required fields
                const requiredFields = [{
                        name: 'event-name',
                        label: 'Event Name'
                    },
                    {
                        name: 'event-slots',
                        label: 'Total Slots'
                    },
                    {
                        name: 'event-description',
                        label: 'Event Description'
                    },
                    {
                        name: 'event-date',
                        label: 'Event Date'
                    },
                    {
                        name: 'event-starting-time',
                        label: 'Event Starting Time'
                    },
                    {
                        name: 'event-end-time',
                        label: 'Event End Time'
                    },
                    {
                        name: 'event-location',
                        label: 'Event Location'
                    },
                    {
                        name: 'event-speaker',
                        label: 'Event Speaker'
                    },
                    {
                        name: 'speaker-description',
                        label: 'Speaker Description'
                    }
                ];

                for (const field of requiredFields) {
                    const input = document.querySelector(`[name="${field.name}"]`);
                    if (!input.value.trim()) {
                        event.preventDefault();
                        alert(`${field.label} is required.`);
                        input.focus();
                        return false;
                    }
                }

                // Validate slots
                const slotsInput = document.querySelector('input[name="event-slots"]');
                const slots = parseInt(slotsInput.value);

                if (isNaN(slots) || slots < 1) {
                    event.preventDefault();
                    alert('Number of slots must be at least 1.');
                    slotsInput.focus();
                    return false;
                }

                // Validate that end time is after start time
                const startTime = document.querySelector('input[name="event-starting-time"]').value;
                const endTime = document.querySelector('input[name="event-end-time"]').value;

                if (startTime >= endTime) {
                    event.preventDefault();
                    alert('Event end time must be after start time.');
                    return false;
                }

                // Validate event date
                const eventDateInput = document.querySelector('input[name="event-date"]');
                const eventDate = new Date(eventDateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                eventDate.setHours(0, 0, 0, 0);

                if (eventDate < today) {
                    event.preventDefault();
                    alert('Event date cannot be in the past.');
                    eventDateInput.focus();
                    return false;
                }

                // For upcoming events, check if date is at least 2 weeks in the future
                const eventStatus = '<?php echo $event['event_status']; ?>';
                if (eventStatus === 'upcoming') {
                    const twoWeeksFromNow = new Date();
                    twoWeeksFromNow.setDate(today.getDate() + 14);
                    twoWeeksFromNow.setHours(0, 0, 0, 0);

                    if (eventDate < twoWeeksFromNow) {
                        event.preventDefault();
                        alert('Event must be scheduled at least 2 weeks from today.');
                        eventDateInput.focus();
                        return false;
                    }
                }
            });

            // Add click event listener for the add speaker button
            const addSpeakerBtn = document.getElementById('add-speaker-button');
            const additionalSpeakersDiv = document.getElementById('additional-speakers');
            let speakerCount = <?php echo count($speakers); ?>; // Start count based on additional speakers

            addSpeakerBtn.addEventListener('click', function() {
                speakerCount++;

                const newSpeaker = document.createElement('div');
                newSpeaker.classList.add('additional-speaker', 'form-row-field2');
                newSpeaker.innerHTML = `
                    <div class="form-field">
                        <label for="additional-speaker-${speakerCount}">Additional Speaker:</label>
                        <input type="text" name="additional-speaker[]" id="additional-speaker-${speakerCount}" placeholder="Add Speaker">
                    </div>
                    <div class="form-field">
                        <label for="additional-description-${speakerCount}">Description:</label>
                        <input type="text" name="additional-description[]" id="additional-description-${speakerCount}" placeholder="Speaker Description">
                    </div>
                    <div class="form-field">
                        <button type="button" class="remove-speaker-button">Remove</button>
                    </div>
                `;

                additionalSpeakersDiv.appendChild(newSpeaker);

                // Attach the event listener to the remove button inside this speaker group
                const removeButton = newSpeaker.querySelector('.remove-speaker-button');
                removeButton.addEventListener('click', function() {
                    newSpeaker.remove(); // Remove the entire speaker group
                });
            });

            // Add event listeners to existing remove buttons
            document.querySelectorAll('.remove-speaker-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.additional-speaker').remove();
                });
            });

            // Add click event listener for the cancel button
            const cancelButton = document.querySelector('.cancel-button');
            cancelButton.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to cancel? All changes will be lost.')) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>