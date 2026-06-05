<?php
// process_registration.php

// Include PHPMailer
require 'vendor/autoload.php';
require 'Thread.php';

// Function to send the registration email
function sendRegistrationEmail($toEmail, $username)
{
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    // Configure PHPMailer settings (SMTP, From, etc.)
    // For simplicity, let's assume you have configured your email settings properly.
    // You may need to configure the settings as per your email provider.
    
    $mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com', getenv('MAIL_FROM_NAME') ?: 'PHPMailer Demo');
    $mail->addAddress($toEmail, $username);
    $mail->Subject = 'Registration Confirmation';
    $mail->Body = "Hello $username,\n\nThank you for registering on our website.";

    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Registration email sent!";
    }
}

// Function to process registration and insert user into the database
function processRegistration($username, $email, $password)
{
    // Insert the user into the database (you'll need to establish a database connection)
    // Replace 'your_database_host', 'your_database_username', 'your_database_password', and 'your_database_name' with your database credentials.
    $conn = new mysqli(getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_USER') ?: 'root', getenv('DB_PASSWORD') ?: '', getenv('DB_NAME') ?: 'phpmailer');
    
    // Check for a successful connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt->bind_param("sss", $username, $email, $passwordHash);
    
    if ($stmt->execute()) {
        echo "User registered successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
    
    // Close the database connection
    $stmt->close();
    $conn->close();

    // Start a background thread to send the registration email
    $thread = new ThreadEmail($email, $username);
    $thread->start();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Call the function to process the registration
    processRegistration($username, $email, $password);
}
?>
