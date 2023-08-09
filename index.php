<?php
session_start();
require_once './mailer.php';

require_once 'db_connection.php';

$loginErrorMessage = '';
$registerErrorMessage = '';
$successMessage = '';

function login($conn)
{
    // Login form handling
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
        $email = $_POST['email'];
        $password = $_POST['loginPass'];

        $sql = "SELECT user_id, hashed_password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $user_id = null;
            $password_hash = null;
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            if (password_verify($password, $password_hash) === true) {
                $_SESSION["user_id"] = $user_id;

                //return to the dashboard user is validated
                if (isset($_SESSION['otp_validated']) && $_SESSION['otp_validated'] == true) {
                    return header('location: /Ashton_Jarvis_Project_COSC_471/index.php?action=dashboard');
                }

                //else make and send a new otp for the user

                // Generate OTP and send it to user's email, if they haven't verified their 
                $otp = rand(100000, 999999);
                $to = $email;
                $subject = "OTP Code for Moss City";
                $message = "Your One Time Password: " . $otp;
                $headers = "From: support@example.com";

                //save the opt with the user id
                $sql = "INSERT INTO OTP (user_id, otp_val, expires) VALUES (?, ?, NOW() + INTERVAL 10 MINUTE)";
                $stmt_otp = $conn->prepare($sql);
                $stmt_otp->bind_param('ss', $user_id, $otp);
                $stmt_otp->execute();
                // mail($to, $subject, $message, $headers);
                sendMail($to, $subject, $message);

                //prompt user to check their email for otp code and verification
                header('location: /Ashton_Jarvis_Project_COSC_471/verify.php');

                // echo '<script>window.location.href = "verify.php";</script>';
                // exit();
            } else {
                $loginErrorMessage = "Invalid login!";
            }
        }
    }
}

function register($conn)
{
    // Registration form handling
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
        $email = $_POST['register_email'];
        $password = password_hash($_POST['register_password'], PASSWORD_DEFAULT);

        if (empty($email) || empty($password)) {
            $registerErrorMessage = "Please fill out all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $registerErrorMessage = "Please enter a valid email address.";
        } else {
            $sql = "INSERT INTO users (email, hashed_password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $email, $password);

            if ($stmt->execute()) {
                $successMessage = "Registration successful. Please log in with your new account.";


            } else {
                $registerErrorMessage = "Registration failed. Please try again.";
            }
        }
    }
}

function verifyCode()
{

}

function valetDirector($action, $body, $conn)
{
    switch ($action) {
        case 'testing_email': {
                echo "testing mail called";
                sendMail('fakedestination@domain.com', 'fake subject', 'fake body');
                break;
            }
        case 'login': {
                // login goest here
                // echo "hello login";
                login($conn);
                break;
            }
        case 'register': {
                register($conn);
                break;
            }
        case 'verify_page': {
                header('Location: /Ashton_Jarvis_Project_COSC_471/verify.php');
                $verifypage = 'this should work';
                echo $verifypage;
                break;
            }
        case 'verify_code': {
                header("Location: dashboard.php");

                break;
            }
        case 'otp_verify': {
                $otpIsValid = isOtpValid($body['otp']);
                //mark the user as validated
                //remove the otpCode from pending otps
                // redirect to dashboard
                break;
            }
        default: {
                echo "Error: action invalid, __ {$action} __";
            }
    }
}

// var_dump($_REQUEST);
// $method = $_REQUEST['METHOD'];
try {

    $action = $_REQUEST['action'];
    // $uri = "{$method}:{$path}";

    $body = [];

    echo $action;
    valetDirector($action, $body, $conn);
} catch (\Throwable $th) {
    echo $th->getTraceAsString();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login and Registration</title>
</head>

<body>
    <h2>Login</h2>
    <form method="post" action="?action=login">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="loginPass">Password:</label>
            <input type="password" id="loginPass" name="loginPass" required>
        </div>
        <div>
            <input type="submit" name="login" value="Login">
        </div>
    </form>
    <p>
        <?php echo $loginErrorMessage; ?>
    </p>

    <h2>Register</h2>
    <form method="post" action="?action=register">
        <div>
            <label for="register_email">Email:</label>
            <input type="email" id="register_email" name="register_email" required>
        </div>
        <div>
            <label for="register_password">Password:</label>
            <input type="password" id="register_password" name="register_password" required>
        </div>
        <div>
            <input type="submit" name="register" value="Register">
        </div>
    </form>
    <p>
        <?php echo $registerErrorMessage; ?>
    </p>
    <p>
        <?php echo $successMessage; ?>
    </p>
</body>

</html>