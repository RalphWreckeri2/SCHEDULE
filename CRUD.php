<?php
include 'DbConnection.php';

// Procedures to handle CRUD operations
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

        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashing the password
        $stmt = $this->conn->prepare("CALL InsertUser(?, ?, ?, ?)"); // Calling the stored procedure
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password); // Binding parameters
        return $stmt->execute(); // Executing the statement
    }

    public function UserExists($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email= ?");
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }

    public function AuthenticateUser($name, $email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE name= ? AND email= ?"); // sabay na lang icheck para mas logical
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $result = $stmt->get_result(); // Getting the result set
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            return $user;
        };

        return false; // User not found or password mismatch
    }

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

    public function InsertResetToken($email, $token)
    {
        $stmt = $this->conn->prepare("call InsertResetToken(?, ?)");
        $stmt->bind_param("ss", $email, $token);
        return $stmt->execute();
    }

    public function UpdatePassword($email, $new_password)
    {
        $stmt = $this->conn->prepare("CALL UpdatePassword(?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);  // For debugging - wag na alisin baka masira eh
        }
        $stmt->bind_param("ss", $email, $new_password);
        return $stmt->execute();
    }

    public function CreateEvent($event_photo, $event_name, $event_category, $event_slots, $event_status, $event_description, $event_date, $event_starting_time, $event_end_time, $event_location, $event_speaker, $speaker_description)
    {
        try {
            // Start session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Check if user_id exists in session
            if (!isset($_SESSION['user_id'])) {
                error_log("Error: user_id not found in session");
                return false;
            }

            $user_id = $_SESSION['user_id'];

            // Prepare the statement
            $stmt = $this->conn->prepare("CALL CreateEvent(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @event_id)");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }

            // Bind parameters
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
                error_log("Binding parameters failed: " . $stmt->error);
                return false;
            }

            // Execute the statement
            $execute_result = $stmt->execute();
            if (!$execute_result) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }

            $stmt->close();

            // Get the event_id
            $result = $this->conn->query("SELECT @event_id as event_id");
            $row = $result->fetch_assoc();
            $event_id = $row['event_id'];

            error_log("Event created successfully in database with ID: " . $event_id);
            return $event_id;
        } catch (Exception $e) {
            error_log("Exception in CreateEvent: " . $e->getMessage());
            return false;
        }
    }

    public function GetEvents($user_id)
    {
        $stmt = $this->conn->prepare("CALL GetEvents(?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there are any results
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC); // Return events as an associative array
        } else {
            return []; // Return an empty array if no events found
        }
    }

    public function ProfileFetcher($user_id)
    {
        // Optional: Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo "<script>alert('Error: You are not logged in.')</script>";
            return false;
        }

        // Prepare the SQL query
        $stmt = $this->conn->prepare("CALL ProfileFetcher(?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Fetch result
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return the user's profile data as an associative array
        } else {
            return false; // No user found
        }
    }

    public function EventFetcher($user_id, $event_category)
    {
        $stmt = $this->conn->prepare("CALL EventFetcher(?, ?)");
        $stmt->bind_param("is", $user_id, $event_category);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC); // Return events as an associative array
            } else {
                return []; // Return an empty array if no events found
            }
        } else {
            error_log("Execute failed: " . $stmt->error);
            return false; // Execution failed   
        }
    }

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

    public function EventDetailsFetcher($event_id)
    {
        $stmt = $this->conn->prepare("SELECT e.event_slots, e.event_date, 
            (SELECT COUNT(*) FROM eventregistration er WHERE er.event_id = e.event_id) AS taken_slots 
            FROM events e WHERE e.event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return event details as an associative array
        } else {
            error_log("No event found for event_id: " . $event_id); // Log the error
            return false; // No event found
        }
    }

    public function GetUserDetails($user_id) {
        $stmt = $this->conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return user details as an associative array
        } else {
            return false; // No user found
        }
    }

    public function EventRegistration($user_id, $event_id, $name, $email, $phone)
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['event_id'])) {
            error_log("Error: user id or event id is not provided");
            return [
                'success' => false,
                'message' => 'User ID or Event ID is missing.'
            ];
        }

        // Check if the user is already registered for the event
        $checkStmt = $this->conn->prepare("SELECT * FROM eventregistration WHERE user_id = ? AND event_id = ?");
        $checkStmt->bind_param("ii", $user_id, $event_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            // User is already registered
            return [
                'success' => false,
                'message' => 'You are already registered for this event.'
            ];
        }

        // Proceed with registration if the user is not already registered
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
}
