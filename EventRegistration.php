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
$message = "";
$registrationSuccess = false;

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

// Return only the modal content for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
?>
    <h2 class="registration-information">Registration Information</h2>
    <div class="event-direction-slots">
        <p class="direction">Fill in the form below to register for the event.</p>
        <p class="slots-available">Number of slots available: <?php echo $availableSlots; ?></p>
    </div>

    <?php if ($message): ?>
        <p class="registration-message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (!$registrationSuccess): ?>
        <form method="post">
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
    <?php endif; ?>
<?php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $user_id = $_SESSION['user_id'];

    // Fetch latest again to prevent race condition
    $eventDetails = $UserManager->EventDetailsFetcher($event_id);
    $availableSlots = $eventDetails['event_slots'] - $eventDetails['taken_slots'];

    if ($availableSlots <= 0) {
        $message = "Sorry, no more slots available.";
    } else {
        $success = $UserManager->EventRegistration($user_id, $event_id, $name, $email, $phone);
        if ($success) {
            $message = "Successfully registered!";
            $registrationSuccess = true;
        } else {
            $message = "Registration failed. You may already be registered.";
        }
    }
}
?>

<div id="eventRegistrationModal" class="event-registration-modal">
    <div class="event-registration-modal-content">
        <span class="event-registration-close-button">&times;</span>

        <div class="event-form-container">
            <h2 class="registration-information">Registration Information</h2>
            <div class="event-direction-slots">
                <p class="direction">Fill in the form below to register for the event.</p>
                <p class="slots-available">Number of slots available: <?php echo $availableSlots; ?></p>
            </div>

            <?php if ($message): ?>
                <p class="registration-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if (!$registrationSuccess): ?>
                <form method="post">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>