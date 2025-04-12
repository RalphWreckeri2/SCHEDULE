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
            die("Prepare failed: " . $this->conn->error);  // For debugging
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
            error_log("User ID from session: " . $user_id);

            // Prepare the statement
            $stmt = $this->conn->prepare("CALL CreateEvent(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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

            error_log("Event created successfully in database");
            $stmt->close();
            return true;
        } catch (Exception $e) {
            error_log("Exception in CreateEvent: " . $e->getMessage());
            return false;
        }
    }
}
