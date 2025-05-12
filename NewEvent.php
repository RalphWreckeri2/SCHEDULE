<?php
include 'DbConnection.php';
include 'CRUD.php';

$UserManager = new UserManager($conn);

// Initialize variables to store form data
$formData = [
    'event-name' => '',
    'event-category' => 'business-and-finance',
    'event-slots' => '',
    'event-status' => 'upcoming',
    'event-description' => '',
    'event-date' => '',
    'event-starting-time' => '',
    'event-end-time' => '',
    'event-location' => '',
    'event-speaker' => '',
    'speaker-description' => '',
    'additional-speakers' => [],
    'additional-descriptions' => []
];

$validationError = '';
$hasError = false;
$tempPhotoPath = '';
$tempPhotoName = '';

// Start or resume session to store temporary file info
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Store form data for repopulation
    foreach ($formData as $key => $value) {
        if (isset($_POST[$key])) {
            $formData[$key] = $_POST[$key];
        }
    }

    // Store additional speakers if any
    if (isset($_POST['additional-speaker']) && is_array($_POST['additional-speaker'])) {
        $formData['additional-speakers'] = $_POST['additional-speaker'];
    }
    if (isset($_POST['additional-description']) && is_array($_POST['additional-description'])) {
        $formData['additional-descriptions'] = $_POST['additional-description'];
    }

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
            $validationError = $label . " is required.";
            $hasError = true;
            break;
        }
    }

    // Client-side validation for slots and time
    if (!$hasError) {
        $eventSlots = (int)$_POST['event-slots'];
        if ($eventSlots < 1) {
            $validationError = 'Number of slots must be at least 1.';
            $hasError = true;
        }
    }

    // Validate event time
    if (!$hasError) {
        $eventStartingTime = $_POST['event-starting-time'];
        $eventEndTime = $_POST['event-end-time'];

        if ($eventStartingTime >= $eventEndTime) {
            $validationError = 'Event end time must be after start time.';
            $hasError = true;
        }
    }

    if (!$hasError) {
        // Handle file upload
        $targetDir = "uploads/";
        $eventPhoto = '';

        // Check if we're using a previously uploaded temp file
        if (
            isset($_POST['use_temp_photo']) && $_POST['use_temp_photo'] == '1' &&
            isset($_SESSION['temp_photo_path']) && file_exists($_SESSION['temp_photo_path'])
        ) {

            $eventPhoto = $_SESSION['temp_photo_path'];
        }
        // Otherwise process the new upload
        else if (isset($_FILES["event-photo"]) && $_FILES["event-photo"]["error"] == UPLOAD_ERR_OK) {
            $imageName = basename($_FILES["event-photo"]["name"]);
            // Create a temporary filename to avoid conflicts
            $tempName = 'temp_' . time() . '_' . $imageName;
            $tempFile = $targetDir . $tempName;

            if (move_uploaded_file($_FILES["event-photo"]["tmp_name"], $tempFile)) {
                // Store the temp file info in session
                $_SESSION['temp_photo_path'] = $tempFile;
                $_SESSION['temp_photo_name'] = $imageName;
                $eventPhoto = $tempFile;
                $tempPhotoPath = $tempFile;
                $tempPhotoName = $imageName;
            } else {
                $validationError = 'Error uploading image.';
                $hasError = true;
            }
        } else if ($_FILES["event-photo"]["error"] != UPLOAD_ERR_NO_FILE) {
            $validationError = 'Error with file upload: ' . $_FILES["event-photo"]["error"];
            $hasError = true;
        } else if (!isset($_SESSION['temp_photo_path']) || !file_exists($_SESSION['temp_photo_path'])) {
            $validationError = 'Please select a photo for the event.';
            $hasError = true;
        }
    }

    if (!$hasError) {
        // If we're still using the temp file, use it
        if (empty($eventPhoto) && isset($_SESSION['temp_photo_path'])) {
            $eventPhoto = $_SESSION['temp_photo_path'];
        }

        // Get form data with validation
        $eventName = $_POST['event-name'];
        $eventCategory = $_POST['event-category'];
        $eventStatus = $_POST['event-status'];
        $eventDescription = $_POST['event-description'];
        $eventDate = $_POST['event-date'];
        $eventStartingTime = $_POST['event-starting-time'];
        $eventEndTime = $_POST['event-end-time'];
        $eventLocation = $_POST['event-location'];
        $eventSpeaker = $_POST['event-speaker'];
        $speakerDescription = $_POST['speaker-description'];

        // Fix category values to match database enum
        // Map form values to database enum values
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

        // Add additional speakers if any
        if (isset($_POST['additional-speaker']) && is_array($_POST['additional-speaker'])) {
            for ($i = 0; $i < count($_POST['additional-speaker']); $i++) {
                if (!empty($_POST['additional-speaker'][$i]) && !empty($_POST['additional-description'][$i])) {
                    $allSpeakers .= " || " . $_POST['additional-speaker'][$i];
                    $allDescriptions .= " || " . $_POST['additional-description'][$i];
                }
            }
        }

        // Validate event date
        $eventDateObj = new DateTime($eventDate);
        $today = new DateTime();
        $today->setTime(0, 0);
        $eventDateObj->setTime(0, 0);

        $interval = $today->diff($eventDateObj)->days;

        if ($eventDateObj < $today) {
            $validationError = 'Event date cannot be in the past.';
            $hasError = true;
        } else if ($interval < 14) {
            $validationError = 'Event must be scheduled at least 2 weeks from today.';
            $hasError = true;
        } else {
            // Call CreateEvent with delimited strings
            $result = $UserManager->CreateEvent(
                $eventPhoto,
                $eventName,
                $eventCategory,
                $eventSlots,
                $eventStatus,
                $eventDescription,
                $eventDate,
                $eventStartingTime,
                $eventEndTime,
                $eventLocation,
                $allSpeakers,
                $allDescriptions
            );

            if (isset($result['success']) && $result['success']) {
                // Clear the temp file info from session on success
                unset($_SESSION['temp_photo_path']);
                unset($_SESSION['temp_photo_name']);

                echo "<script>
                alert('Event created successfully!');
                window.location.href = 'MyEvents.php';
                </script>";
                exit();
            } else {
                $errorMessage = isset($result['error']) ? $result['error'] : 'An unknown error occurred';
                $validationError = $errorMessage;
                $hasError = true;
            }
        }
    }
}

// Get temp photo info from session if available
if (isset($_SESSION['temp_photo_path']) && file_exists($_SESSION['temp_photo_path'])) {
    $tempPhotoPath = $_SESSION['temp_photo_path'];
    $tempPhotoName = $_SESSION['temp_photo_name'];
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
        <!-- Sidebar content remains unchanged -->
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
                <h1>New Event</h1>
                <p>Fill out the following details to create a new event.</p>
            </div>

            <div class="separator-line"></div>

            <?php if ($hasError): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($validationError); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="NewEvent.php" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-field">
                        <label for="event-photo">Upload event cover photo (.jpeg, .jpg, .png):</label>
                        <div class="file-input-container">
                            <?php if (!empty($tempPhotoPath)): ?>
                                <div class="temp-file-info">
                                    <span class="temp-file-name">Current file: <?php echo htmlspecialchars($tempPhotoName); ?></span>
                                    <input type="hidden" name="use_temp_photo" value="1">
                                    <button type="button" id="change-photo-btn">Change Photo</button>
                                </div>
                                <div id="new-photo-input" style="display: none;">
                                    <input type="file" name="event-photo" id="event-photo" accept=".jpeg, .jpg, .png">
                                </div>
                            <?php else: ?>
                                <input type="file" name="event-photo" id="event-photo" accept=".jpeg, .jpg, .png" required>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="event-name">Event Name:</label>
                        <input type="text" name="event-name" placeholder="Event Name" value="<?php echo htmlspecialchars($formData['event-name']); ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-row-field">
                            <label for="event-category">Event Category:</label>
                            <select name="event-category" required>
                                <option value="business-and-finance" <?php echo $formData['event-category'] == 'business-and-finance' ? 'selected' : ''; ?>>Business & Finance</option>
                                <option value="technology-and-innovation" <?php echo $formData['event-category'] == 'technology-and-innovation' ? 'selected' : ''; ?>>Technology & Innovation</option>
                                <option value="health-and-wellness" <?php echo $formData['event-category'] == 'health-and-wellness' ? 'selected' : ''; ?>>Health & Wellness</option>
                                <option value="personal-and-professional-development" <?php echo $formData['event-category'] == 'personal-and-professional-development' ? 'selected' : ''; ?>>Personal & Professional Development</option>
                            </select>
                        </div>
                        <div class="form-row-field">
                            <label for="event-slots">Total Slots:</label>
                            <input type="number" name="event-slots" placeholder="Total Slots" value="<?php echo htmlspecialchars($formData['event-slots']); ?>" min="1" required>
                        </div>
                        <div class="form-row-field">
                            <label for="event-status">Event Status:</label>
                            <select name="event-status" required>
                                <option value="upcoming" <?php echo $formData['event-status'] == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo $formData['event-status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="past" <?php echo $formData['event-status'] == 'past' ? 'selected' : ''; ?>>Past</option>
                            </select>
                        </div>
                    </div>

                    <!-- Rest of the form fields remain the same with value attributes -->
                    <div class="form-field">
                        <label for="event-description">Event Description:</label>
                        <textarea name="event-description" placeholder="Event Description" required><?php echo htmlspecialchars($formData['event-description']); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-row-field">
                            <label for="event-date">Event Date:</label>
                            <input type="date" name="event-date" value="<?php echo htmlspecialchars($formData['event-date']); ?>" required>
                        </div>
                        <div class="form-row-field">
                            <label for="event-starting-time">Event Starting Time:</label>
                            <input type="time" name="event-starting-time" value="<?php echo htmlspecialchars($formData['event-starting-time']); ?>" required>
                        </div>
                        <div class="form-row-field">
                            <label for="event-end-time">Event End Time:</label>
                            <input type="time" name="event-end-time" value="<?php echo htmlspecialchars($formData['event-end-time']); ?>" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="event-location">Event Location:</label>
                        <input type="text" name="event-location" placeholder="Event Location" value="<?php echo htmlspecialchars($formData['event-location']); ?>" required>
                    </div>
                    <div class="form-row-field2">
                        <div class="form-field">
                            <label for="event-speaker">Event Speaker:</label>
                            <input type="text" name="event-speaker" placeholder="Add Speaker" value="<?php echo htmlspecialchars($formData['event-speaker']); ?>" required>
                        </div>
                        <div class="form-field">
                            <label for="speaker-description">Description:</label>
                            <input type="text" name="speaker-description" placeholder="Speaker Description" value="<?php echo htmlspecialchars($formData['speaker-description']); ?>" required>
                        </div>
                        <div class="form-field">
                            <div id="additional-speakers">
                                <?php if (!empty($formData['additional-speakers'])): ?>
                                    <?php for ($i = 0; $i < count($formData['additional-speakers']); $i++): ?>
                                        <div class="additional-speaker form-row-field2">
                                            <div class="form-field">
                                                <label for="additional-speaker-<?php echo $i + 1; ?>">Additional Speaker:</label>
                                                <input type="text" name="additional-speaker[]" id="additional-speaker-<?php echo $i + 1; ?>" placeholder="Add Speaker" value="<?php echo htmlspecialchars($formData['additional-speakers'][$i]); ?>" required>
                                            </div>
                                            <div class="form-field">
                                                <label for="additional-description-<?php echo $i + 1; ?>">Description:</label>
                                                <input type="text" name="additional-description[]" id="additional-description-<?php echo $i + 1; ?>" placeholder="Speaker Description" value="<?php echo htmlspecialchars($formData['additional-descriptions'][$i]); ?>" required>
                                            </div>
                                            <div class="form-field">
                                                <button type="button" class="remove-speaker-button">Remove</button>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="add-speaker-button-container">
                        <button type="button" class="add-speaker-button" id="add-speaker-button">Add Speaker</button>
                    </div>
                    <div class="button-row">
                        <button type="button" class="cancel-button">Cancel</button>
                        <button type="submit" class="submit-button">Create</button>
                    </div>
                </div>
            </form>

            <div class="separator-line"></div>

            <!-- Contact section remains unchanged -->
            <h2>Contact Us</h2>
            <p class="description">
                Have questions or need assistance? We're here to help! Feel free to reach out to us for any inquiries about event registrations, technical support, or general concerns.
            </p>

            <div class="contact-info">
                <!-- Contact info remains unchanged -->
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

                // Check if this nav item corresponds to the current page
                if (currentPageName === hrefPage ||
                    (currentPageName === 'Dashboard.php' && item.id === 'dashboard') ||
                    (currentPageName === '' && item.id === 'dashboard')) {
                    item.classList.add('active');
                    console.log('Set active:', item.id);
                }
            });

            // Handle change photo button
            const changePhotoBtn = document.getElementById('change-photo-btn');
            if (changePhotoBtn) {
                changePhotoBtn.addEventListener('click', function() {
                    document.getElementById('new-photo-input').style.display = 'block';
                    document.querySelector('.temp-file-info').style.display = 'none';
                    document.querySelector('input[name="use_temp_photo"]').value = '0';
                    document.getElementById('event-photo').required = true;
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
            });

            const addSpeakerBtn = document.getElementById('add-speaker-button');
            const additionalSpeakersDiv = document.getElementById('additional-speakers');
            let speakerCount = <?php echo !empty($formData['additional-speakers']) ? count($formData['additional-speakers']) : 0; ?>;

            addSpeakerBtn.addEventListener('click', function() {
                speakerCount++;

                const newSpeaker = document.createElement('div');
                newSpeaker.classList.add('additional-speaker', 'form-row-field2');
                newSpeaker.innerHTML = `
                    <div class="form-field">
                        <label for="additional-speaker-${speakerCount}">Additional Speaker:</label>
                        <input type="text" name="additional-speaker[]" id="additional-speaker-${speakerCount}" placeholder="Add Speaker" required>
                    </div>
                    <div class="form-field">
                        <label for="additional-description-${speakerCount}">Description:</label>
                        <input type="text" name="additional-description[]" id="additional-description-${speakerCount}" placeholder="Speaker Description" required>
                    </div>
                    <div class="form-field">
                        <button type="button" class="remove-speaker-button">Remove</button>
                    </div>
                `;

                additionalSpeakersDiv.appendChild(newSpeaker);

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

            const cancelButton = document.querySelector('.cancel-button');
            cancelButton.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel? All entered information will be lost.')) {
                    // Clear the session storage for the temp file
                    fetch('clear_temp_file.php')
                        .then(response => {
                            document.querySelector('form').reset();
                            additionalSpeakersDiv.innerHTML = ''; // Clear additional speakers
                            window.location.href = 'NewEvent.php'; // Reload the page to clear everything
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.querySelector('form').reset();
                            additionalSpeakersDiv.innerHTML = '';
                            window.location.href = 'NewEvent.php';
                        });
                }
            });
        });
    </script>

</body>

</html>