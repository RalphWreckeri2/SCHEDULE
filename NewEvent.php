<?php
include 'DbConnection.php';
include 'CRUD.php'; // This is where your createUser() function lives

$UserManager = new UserManager($conn);

// After form submission, add detailed logging
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // File upload handling
    $targetDir = "uploads/";
    if (isset($_FILES["event-photo"]) && $_FILES["event-photo"]["error"] == UPLOAD_ERR_OK) {
        $imageName = basename($_FILES["event-photo"]["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["event-photo"]["tmp_name"], $targetFile)) {
            $eventPhoto = $targetFile;
            // error_log("File uploaded successfully to: " . $eventPhoto);
        } else {
            // error_log("Failed to move uploaded file. Error: " . $_FILES["event-photo"]["error"]);
            echo "<script>alert('Error uploading image.');</script>";
            exit;
        }
    } else {
        // error_log("No file uploaded or error occurred. Error code: " . $_FILES["event-photo"]["error"]);
        echo "<script>alert('No file uploaded or an error occurred.');</script>";
        exit;
    }

    // Get form data with validation
    $eventName = $_POST['event-name'];
    $eventCategory = $_POST['event-category'];
    $eventSlots = (int)$_POST['event-slots'];
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

    // Call CreateEvent with delimited strings
    $success = $UserManager->CreateEvent(
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

    if ($success) {
        echo "<script>
        alert('Event created successfully!');
        window.location.href = 'MyEvents.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Erroor');</script>";
    }
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
            <img src="SCHEDULE RBG.png" alt="Schedule Logo" class="logo1">
        </div>

        <div class="separator"></div>

        <div class="nav-menu">
            <a href="Dashboard.php" class="nav-item" id="dashboard">
                <img src="dashboard-icon.png" alt="Dashboard" class="nav-icon">
                <span>Dashboard</span>
            </a>
            <a href="MyEvents.php" class="nav-item" id="my-events">
                <img src="my-events-icon.png" alt="My Events" class="nav-icon">
                <span>My Events</span>
            </a>
            <a href="NewEvent.php" class="nav-item" id="new-event">
                <img src="new-event-icon.png" alt="New Event" class="nav-icon">
                <span>New Event</span>
            </a>
            <a href="AvailableEvent.php" class="nav-item" id="available-events">
                <img src="available-events-icon.png" alt="Available Events" class="nav-icon">
                <span>Available Events</span>
            </a>
        </div>

        <div class="bottom-menu">
            <a href="Profile.php" class="nav-item" id="profile">
                <img src="profile-icon.png" alt="Profile" class="nav-icon">
                <span>Profile</span>
            </a>
            <a href="Logout.php" class="nav-item" id="logout">
                <img src="logout-icon.png" alt="Log Out" class="nav-icon">
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

            <form method="POST" action="NewEvent.php" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-field">
                        <label for="event-photo">Upload event cover photo (.jpeg, .jpg, .png):</label>
                        <input type="file" name="event-photo" accept=".jpeg, .jpg, .png" required>
                    </div>
                    <div class="form-field">
                        <label for="event-name">Event Name:</label>
                        <input type="text" name="event-name" placeholder="Event Name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-row-field">
                            <label for="event-category">Event Category:</label>
                            <select name="event-category" required>
                                <option value="business-and-finance">Business & Finance</option>
                                <option value="technology-and-innovation">Technology & Innovation</option>
                                <option value="health-and-wellness">Health & Wellness</option>
                                <option value="personal-and-professional-development">Personal & Professional Development</option>
                            </select>
                        </div>
                        <div class="form-row-field">
                            <label for="event-slots">Total Slots:</label>
                            <input type="number" name="event-slots" placeholder="Total Slots" required>
                        </div>
                        <div class="form-row-field">
                            <label for="event-status">Event Status:</label>
                            <select name="event-status" required>
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="past">Past</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="event-description">Event Description:</label>
                        <textarea name="event-description" placeholder="Event Description" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-row-field">
                            <label for="event-date">Event Date:</label>
                            <input type="date" name="event-date" required>
                        </div>
                        <div class="form-row-field">
                            <label for="event-starting-time">Event Starting Time:</label>
                            <input type="time" name="event-starting-time" required>
                        </div>
                        <div class="form-row-field">
                            <label for="event-end-time">Event End Time:</label>
                            <input type="time" name="event-end-time" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="event-location">Event Location:</label>
                        <input type="text" name="event-location" placeholder="Event Location" required>
                    </div>
                    <div class="form-row-field2">
                        <div class="form-field">
                            <label for="event-speaker">Event Speaker:</label>
                            <input type="text" name="event-speaker" placeholder="Add Speaker" required>
                        </div>
                        <div class="form-field">
                            <label for="speaker-description">Description:</label>
                            <input type="text" name="speaker-description" placeholder="Speaker Description" required>
                        </div>
                        <div class="form-field">
                            <div id="additional-speakers"></div>
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

            <h2>Contact Us</h2>
            <p class="description">
                Have questions or need assistance? We're here to help! Feel free to reach out to us for any inquiries about event registrations, technical support, or general concerns.
            </p>

            <div class="contact-info">
                <div class="contact-item">
                    <img src="address-icon.png" alt="Address" class="contact-icon">
                    <div class="contact-text">
                        <strong>Address:</strong> 1234 Rizal Street, Makati City, Metro Manila, Philippines
                    </div>
                </div>

                <div class="contact-item">
                    <img src="email-icon.png" alt="Email" class="contact-icon">
                    <div class="contact-text">
                        <strong>Email:</strong> support@scheduleevents.ph
                    </div>
                </div>

                <div class="contact-item">
                    <img src="phone-icon.png" alt="Phone" class="contact-icon">
                    <div class="contact-text">
                        <strong>Phone:</strong> (+63) 912-345-6789
                    </div>
                </div>

                <div class="contact-item">
                    <img src="social-icon.png" alt="Socials" class="contact-icon">
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

            // Add click event listener for the add speaker button
            // Add click event listener for the add speaker button
            const addSpeakerBtn = document.getElementById('add-speaker-button');
            const additionalSpeakersDiv = document.getElementById('additional-speakers');
            let speakerCount = 0;

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
                `;

                additionalSpeakersDiv.appendChild(newSpeaker);
            });

            // Add click event listener for the cancel button
            const cancelButton = document.querySelector('.cancel-button');
            cancelButton.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel? All entered information will be lost.')) {
                    document.querySelector('form').reset();
                    additionalSpeakersDiv.innerHTML = ''; // Clear additional speakers
                }
            });

        });
    </script>

</body>

</html>