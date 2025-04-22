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

$availableSlots = 0;
$deadline = "N/A";
$isAlreadyRegistered = false;
$registrationSuccess = false; // Initialize the registration success variable

// Fetch event details
if (isset($_SESSION['event_id'])) {
    $event_id = $_SESSION['event_id'];
    $eventDetails = $UserManager->EventDetailsFetcher($event_id);

    if ($eventDetails) {
        $totalSlots = $eventDetails['event_slots'];
        $takenSlots = $eventDetails['taken_slots'];
        $eventDate = $eventDetails['event_date'];

        $availableSlots = $totalSlots - $takenSlots;
        $deadline = date('F j, Y', strtotime($eventDate . ' -1 day'));
    }

    // Check if the user is already registered for the event
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $checkStmt = $conn->prepare("SELECT * FROM eventregistration WHERE user_id = ? AND event_id = ?");
        $checkStmt->bind_param("ii", $user_id, $event_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $isAlreadyRegistered = true;
        }
    }
}

// Fetch user details
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $userDetails = $UserManager->GetUserDetails($user_id);

    if ($userDetails) {
        $userName = $userDetails['name'];
        $userEmail = $userDetails['email'];
        $userPhone = $userDetails['phone'];
    }
}

// Handle cancellation request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'cancel_registration') {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];

    // Call the CancelRegistration method
    $result = $UserManager->CancelRegistration($user_id, $event_id);

    // Return the result as JSON
    echo json_encode($result);
    exit;
}

// Handle registration request
if ($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_POST['action']) || $_POST['action'] !== 'cancel_registration')) {
    $event_id = $_POST['event_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $user_id = $_SESSION['user_id'];

    // Call the EventRegistration method
    $result = $UserManager->EventRegistration($user_id, $event_id, $name, $email, $phone);

    // Return the message as JSON for the modal
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message']
    ]);
    exit;
}

// If this is a GET request and not a POST request, return the form content
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['event_id'])) {
?>
    <h2 class="registration-information">Registration Information</h2>
    <div class="event-direction-slots">
        <p class="direction">
            <?php if (!$isAlreadyRegistered): ?>
                Fill in the form below to register for the event.
            <?php else: ?>
                You are already registered for this event.
            <?php endif; ?>
        </p>
        <p class="slots-available">Number of slots available: <?php echo $availableSlots; ?></p>
    </div>

    <?php if (!$isAlreadyRegistered): ?>
        <form id="event-registration-form" method="post">
            <input type="hidden" name="event_id" value="<?php echo isset($_SESSION['event_id']) ? htmlspecialchars($_SESSION['event_id']) : ''; ?>">
            <div class="form-field">
                <input type="text" name="name" placeholder="Name" value="<?php echo isset($userName) ? htmlspecialchars($userName) : ''; ?>" required>
            </div>
            <div class="form-field">
                <input type="email" name="email" placeholder="Email" value="<?php echo isset($userEmail) ? htmlspecialchars($userEmail) : ''; ?>" required>
            </div>
            <div class="form-field">
                <input type="tel" name="phone" placeholder="Phone Number" value="<?php echo isset($userPhone) ? htmlspecialchars($userPhone) : ''; ?>" required>
            </div>
            <div class="form-submit">
                <button type="submit" class="btn btn-primary" <?php echo ($availableSlots <= 0) ? 'disabled' : ''; ?>>REGISTER</button>
            </div>
            <div class="deadline-wrapper">
                <p class="deadline">Registration Deadline: <?php echo $deadline; ?></p>
            </div>
        </form>
    <?php else: ?>
        <div class="already-registered">
            <p>You are already registered for this event.</p>
            <div class="form-submit">
                <button type="button" class="btn btn-danger" id="cancel-registration-button" data-event-id="<?php echo htmlspecialchars($_SESSION['event_id']); ?>">CANCEL REGISTRATION</button>
            </div>
        </div>
    <?php endif; ?>
<?php
    exit;
}
?>

<div id="eventRegistrationModal" class="event-registration-modal">
    <div class="event-registration-modal-content">
        <span class="event-registration-close-button">&times;</span>
        <div id="modal-content" class="event-form-container">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
</div>

<script>
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

    document.addEventListener('DOMContentLoaded', function() {
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
            fetch(`EventRegistration.php?event_id=${eventId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modal-content').innerHTML = data;
                    
                    // Setup event handlers for the new content
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
    });
</script>

