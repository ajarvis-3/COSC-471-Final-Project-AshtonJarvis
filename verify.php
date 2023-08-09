<?php


session_start();
$errorMessage = '';


// if (!isset($_SESSION['user_id']) || !isset($_SESSION['otp'])) {
//     header("Location: index.php");
//     exit();
// }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["authenticate"])) {
    $otp = $_POST['otp'];

    // Check if OTP matches stored OTP
    if ($otp == $_SESSION['otp']) {
        // OTP is verified, redirect to dashboard
        $_SESSION['otp_validated'] = true; 
        header("Location: dashboard.php");
        exit();
    } else {
        $errorMessage = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Verify OTP</title>
</head>

<body>
    <h2>Verify OTP</h2>
    <form method="post" action="index.php?action=verify_code">
        <div>
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" required>
        </div>
        <div>
            <input type="submit" name="authenticate" value="Verify">
        </div>
    </form>
    <p>
        <?php echo $errorMessage; ?>
    </p>
</body>

</html>