<?php
include 'DbConnection.php';

class UserManager
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Function to create a new user
    public function InsertUser($name, $email, $phone, $password)
    {
        if ($this->AuthenticateUser($name, $email, $password)) { // Check if the user already exists
            return false; // User already exists
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("CALL InsertUser(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
        return $stmt->execute();
    }

    // checks if the user already exists in the users table - situated in the creat account file
    public function UserExists($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email= ?");
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }

    // allows the already created user to push through when signing in - situated in the sign in file
    public function AuthenticateUser($name, $email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE name= ? AND email= ?");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // fetches row by row

        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            return $user;
        };

        return false;
    }

    // checking if the name is in the users table 
    // purpose: to retain the correct credentials in a field if the signing in did not push through
    public function isNameValid($name)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    // checking if the email is in the users table 
    // purpose: to retain the correct credentials in a field if the signing in did not push through
    public function isEmailValid($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    // inserts the reset token in the password resets table - 5 characters (capital letters and/or 0-9)
    public function InsertResetToken($email, $token)
    {
        $stmt = $this->conn->prepare("call InsertResetToken(?, ?)");
        $stmt->bind_param("ss", $email, $token);
        return $stmt->execute();
    }

    // updates the user's password in the users table
    public function UpdatePassword($email, $new_password)
    {
        $stmt = $this->conn->prepare("CALL UpdatePassword(?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);  // For debugging - wag na alisin baka masira eh
        }
        $stmt->bind_param("ss", $email, $new_password);
        return $stmt->execute();
    }

    // creates an event 
    public function CreateEvent($event_photo, $event_name, $event_category, $event_slots, $event_status, $event_description, $event_date, $event_starting_time, $event_end_time, $event_location, $event_speaker, $speaker_description)
    {
        try {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if (!isset($_SESSION['user_id'])) {
                return ['success' => false, 'error' => 'User not logged in'];
            }

            $user_id = $_SESSION['user_id'];

            // Validate slots
            if (!is_numeric($event_slots) || $event_slots < 1) {
                return ['success' => false, 'error' => 'Invalid number of slots. Must be at least 1.'];
            }

            // Validate time logic
            if ($event_starting_time >= $event_end_time) {
                return ['success' => false, 'error' => 'Event end time must be after start time.'];
            }

            // Check for overlapping events
            $check = $this->conn->prepare("SELECT * FROM events 
                WHERE user_id = ? 
                AND event_date = ? 
                AND (
                    (event_starting_time <= ? AND event_end_time > ?) OR  /* New event starts during existing event */
                    (event_starting_time < ? AND event_end_time >= ?) OR  /* New event ends during existing event */
                    (event_starting_time >= ? AND event_end_time <= ?)    /* New event is contained within existing event */
                )");

            if (!$check) {
                error_log("Prepare failed (overlap check): " . $this->conn->error);
                return ['success' => false, 'error' => 'Database error during overlap check'];
            }

            $check->bind_param(
                "isssssss",
                $user_id,
                $event_date,
                $event_starting_time,
                $event_starting_time,  // For first condition
                $event_end_time,
                $event_end_time,            // For second condition
                $event_starting_time,
                $event_end_time        // For third condition
            );

            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $check->close();
                return ['success' => false, 'error' => 'You already have an event scheduled during this time.'];
            }
            $check->close();

            // Begin transaction
            $this->conn->begin_transaction();

            // Proceed with creating the event
            $stmt = $this->conn->prepare("CALL CreateEvent(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @event_id)");
            if (!$stmt) {
                $this->conn->rollback();
                error_log("Prepare failed: " . $this->conn->error);
                return ['success' => false, 'error' => 'Database error while preparing statement'];
            }

            $bind_result = $stmt->bind_param(
                "isssissssssss",
                $user_id,
                $event_photo,
                $event_name,
                $event_category,
                $event_slots,
                $event_status,
                $event_description,
                $event_date,
                $event_starting_time,
                $event_end_time,
                $event_location,
                $event_speaker,
                $speaker_description
            );

            if (!$bind_result) {
                $this->conn->rollback();
                error_log("Binding parameters failed: " . $stmt->error);
                return ['success' => false, 'error' => 'Database error while binding parameters'];
            }

            $execute_result = $stmt->execute();
            if (!$execute_result) {
                $this->conn->rollback();
                error_log("Execute failed: " . $stmt->error);
                return ['success' => false, 'error' => 'Database error while executing statement'];
            }

            $stmt->close();

            // Get the event_id
            $result = $this->conn->query("SELECT @event_id as event_id");
            $row = $result->fetch_assoc();
            $event_id = $row['event_id'];

            // Commit transaction
            $this->conn->commit();

            error_log("Event created successfully in database with ID: " . $event_id);
            return ['success' => true, 'event_id' => $event_id];
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Exception in CreateEvent: " . $e->getMessage());
            return ['success' => false, 'error' => 'An unexpected error occurred'];
        }
    }

    // fetch events created by the user - situated in my events page
    public function GetEvents($user_id)
    {
        $stmt = $this->conn->prepare("CALL GetEvents(?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC); // Return events as an associative array (all)
        } else {
            return []; // Return an empty array if no events found
        }
    }

    // fetch user information
    public function ProfileFetcher($user_id)
    {
        // double check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo "<script>alert('Error: You are not logged in.')</script>";
            return false;
        }

        $stmt = $this->conn->prepare("CALL ProfileFetcher(?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return the user's profile data as an associative array (row by row)
        } else {
            return false;
        }
    }

    // for fetching events based on category - situated in available events
    public function EventFetcher($user_id, $event_category)
    {
        $stmt = $this->conn->prepare("CALL EventFetcher(?, ?)");
        $stmt->bind_param("is", $user_id, $event_category);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC); // Return events as an associative array (all)
            } else {
                return []; // Return an empty array if no events found
            }
        } else {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
    }

    // fetching events for dashbaord - situated in the dahsboard
    public function EventFetcherInDb($user_id)
    {
        $stmt = $this->conn->prepare("CALL EventFetcherInDb(?)");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                return [];
            }
        } else {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
    }

    // fetching surface event details for event registration modal
    public function EventDetailsFetcher($event_id)
    {
        $stmt = $this->conn->prepare("SELECT e.event_slots, e.event_date, 
            (SELECT COUNT(*) FROM eventregistration er WHERE er.event_id = e.event_id) AS taken_slots 
            FROM events e WHERE e.event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return event details as an associative array (row by row)
        } else {
            error_log("No event found for event_id: " . $event_id); // Log the error
            return false; // No event found
        }
    }

    // for automatic appearance of user details for event registration modal
    public function GetUserDetails($user_id)
    {
        $stmt = $this->conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return user details as an associative array (row by row)
        } else {
            return false; // No user found
        }
    }

    // for registering a user to a specific event
    public function EventRegistration($user_id, $event_id, $name, $email, $phone)
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['event_id'])) {
            error_log("Error: user id or event id is not provided");
            return [
                'success' => false,
                'message' => 'User ID or Event ID is missing.'
            ];
        }

        // Check if the user is already registered for the event using IsUserRegistered function
        if ($this->IsUserRegistered($user_id, $event_id)) {
            return [
                'success' => false,
                'message' => 'You are already registered for this event.'
            ];
        }

        // Proceed with registration if the user is not yet registered
        $stmt = $this->conn->prepare('CALL EventRegistration(?, ?, ?, ?, ?)');
        $stmt->bind_param("iisss", $user_id, $event_id, $name, $email, $phone);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Registration successful!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again later.'
            ];
        }
    }

    // for canceling event registration of a user
    public function CancelRegistration($user_id, $event_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM eventregistration WHERE user_id = ? AND event_id = ?");
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->conn->error
            ];
        }

        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();

        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            return [
                'success' => true,
                'message' => 'Your registration has been canceled.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Your registration cannot be canceled. It may not exist.'
            ];
        }
    }

    // for updating user's profile picture - must be in uploads folder
    public function UpdateProfile($user_id, $user_profile)
    {
        $stmt = $this->conn->prepare("CALL UpdateProfile(?, ?)");
        $stmt->bind_param("is", $user_id, $user_profile);
        $stmt->execute();
    }

    // for updating an existing event
    public function UpdateEvent(
        $event_id,
        $user_id = null,
        $event_photo = null,
        $event_name = null,
        $event_category = null,
        $event_slots = null,
        $event_status = null,
        $event_description = null,
        $event_date = null,
        $event_starting_time = null,
        $event_end_time = null,
        $event_location = null,
        $event_speaker = null,
        $speaker_description = null
    ) {

        $stmt = $this->conn->prepare("CALL UpdateEvent(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "iisssissssssss",
            $event_id,
            $user_id,
            $event_photo,
            $event_name,
            $event_category,
            $event_slots,
            $event_status,
            $event_description,
            $event_date,
            $event_starting_time,
            $event_end_time,
            $event_location,
            $event_speaker,
            $speaker_description
        );

        $stmt->execute();

        $message = null;
        $success = false;

        $stmt->store_result();
        $stmt->bind_result($message, $success);

        $response = ['message' => null, 'success' => false];
        if ($stmt->fetch()) {
            $response = ['message' => $message, 'success' => $success];
        }

        $stmt->close();

        return $response;
    }

    // Get complete event details by ID - situated in the view event page and edit event page
    public function GetEventById($event_id)
    {
        $stmt = $this->conn->prepare("SELECT e.*, 
        (SELECT COUNT(*) FROM eventregistration er WHERE er.event_id = e.event_id) AS taken_slots 
        FROM events e WHERE e.event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    // Check if a user is registered for an event
    public function IsUserRegistered($user_id, $event_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM eventregistration WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Get the number of registered users for an event (taken slots)
    public function GetRegisteredCount($event_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM eventregistration WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    // for fetching the list of participants on a specific event - situated in the participants list page
    public function GetEventParticipants($event_id)
    {
        error_log("Getting participants for event ID: " . $event_id);

        $stmt = $this->conn->prepare("CALL GetEventParticipants(?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $event_id);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $participants = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Found " . count($participants) . " participants");
            return $participants;
        } else {
            error_log("No participants found or get_result failed.");
            return [];
        }
    }

    // for deleting a participant
    public function DeleteParticipant($user_id, $event_id)
    {
        // Check if user_id is empty
        if (empty($user_id)) {
            error_log("DeleteParticipant: Empty user_id provided");
            return false;
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM eventregistration WHERE user_id = ? AND event_id = ?");
            if ($stmt === false) {
                error_log("DeleteParticipant: Prepare statement failed: " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("ii", $user_id, $event_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("DeleteParticipant: Exception: " . $e->getMessage());
            return false;
        }
    }

    // for deleting an event
    public function DeleteEvent($event_id)
    {
        // First delete all registrations for this event
        $stmt1 = $this->conn->prepare("DELETE FROM eventregistration WHERE event_id = ?");
        $stmt1->bind_param("i", $event_id);
        $stmt1->execute();

        // Then delete the event
        $stmt2 = $this->conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt2->bind_param("i", $event_id);
        return $stmt2->execute();
    }

    // for updating event status using the current date as guide - edit event page
    public function UpdateEventStatusAutomatically($event_id = null)
    {
        // If no specific event_id is provided, update all events
        if ($event_id === null) {
            $query = "UPDATE events 
                 SET event_status = 
                    CASE 
                        WHEN event_date > CURDATE() THEN 'upcoming'
                        WHEN event_date < CURDATE() THEN 'past'
                        WHEN event_date = CURDATE() AND event_end_time < CURTIME() THEN 'past'
                        WHEN event_date = CURDATE() THEN 'ongoing'
                    END";
            return $this->conn->query($query);
        } else {
            // Update specific event
            $stmt = $this->conn->prepare("
            UPDATE events 
            SET event_status = 
                CASE 
                    WHEN event_date > CURDATE() THEN 'upcoming'
                    WHEN event_date < CURDATE() THEN 'past'
                    WHEN event_date = CURDATE() AND event_end_time < CURTIME() THEN 'past'
                    WHEN event_date = CURDATE() THEN 'ongoing'
                END
            WHERE event_id = ?
        ");
            $stmt->bind_param("i", $event_id);
            return $stmt->execute();
        }
    }

    // this is for getting the events joined by the user - situated in the my events page
    public function GetUserEventsByStatus($user_id, $status)
    {
        $stmt = $this->conn->prepare("CALL UserEventsBySimpleStatus(?, ?)");
        $stmt->bind_param("is", $user_id, $status);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $events = [];

            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }

            $stmt->close();
            return $events;
        } else {
            $stmt->close();
            return [];
        }
    }
}
