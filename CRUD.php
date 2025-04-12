<?php 
include 'DbConnection.php';

// Procedures to handle CRUD operations
class UserManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Function to create a new user
    public function InsertUser($name, $email, $phone, $password) {
        if ($this->AuthenticateUser($name, $email, $password)) { // Check if the user already exists
            return false; // User already exists
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashing the password
        $stmt = $this->conn->prepare("CALL InsertUser(?, ?, ?, ?)"); // Calling the stored procedure
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password); // Binding parameters
        return $stmt->execute(); // Executing the statement
    }

    public function AuthenticateUser($name, $email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE name= ? AND email= ?"); // sabay na lang icheck para mas logical
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $result = $stmt->get_result(); // Getting the result set
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        };

        return false; // User not found or password mismatch
    }

    public function isNameValid($name) {
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

    public function isEmailValid($email) {
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

    public function InsertResetToken($email, $token) {
        $stmt = $this->conn->prepare ("call InsertResetToken(?, ?)");
        $stmt->bind_param("ss", $email, $token);
        return $stmt->execute();
    }

    public function UpdatePassword($email, $new_password) {
        $stmt = $this->conn->prepare("CALL UpdatePassword(?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);  // For debugging
        }
        $stmt->bind_param("ss", $email, $new_password);
        return $stmt->execute();
    }
}
?>