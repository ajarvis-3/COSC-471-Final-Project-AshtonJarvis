<?php
// Point to our database connectivity helper file
require_once 'db_connection.php';

// Conditional to test if the user successfully POSTED their username and password to the server
if (isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if input it valid
    if (empty($username) || empty($password)) {
        // Show an error in the event of malformed or empty data.
        $error_message = "Please enter both username and password.";
    } else {
        // Check that user credentials are verified.
        $query = "SELECT user_id, hashed_password FROM User WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
        $stmt->close();

        if ($hashed_password && password_verify($password, $hashed_password)) {
            // Randomly generates a one-time password between 100000 and 999999.
            $otp = rand(100000, 999999);

            // Insert OTP in our DB
            $query = "INSERT INTO OTP (user_id, otp_val, expires) VALUES (?, ?, NOW() + INTERVAL 10 MINUTE)"; //Timestamp
            $stmt = $conn->prepare($query);
            $stmt->bind_param('is', $user_id, $otp);
            $stmt->execute();
            $stmt->close();

            // Send OTP to the email address provided
            $to = $_POST['username'];
            $subject = "Your One-Time Password (OTP)";
            $message = "Your OTP is: " . $otp;

            $headers = "From: your_email@example.com" . "\r\n"; // Replace with a valid sender email address


            if (mail($to, $subject, $message, $headers)) {
                // Redirects user
                header("Location: otp_verification.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>
    <?php if (isset($error_message)) { ?>
        <p style="color: red;">
            <?php echo $error_message; ?>
        </p>
    <?php } ?>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" name="login_submit" value="Login">
    </form>
</body>

</html>