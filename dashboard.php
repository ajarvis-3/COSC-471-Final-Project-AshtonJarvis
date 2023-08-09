<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once 'db_connection.php';

$sql = "SELECT email, registered_time FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($email, $registered_time);
$stmt->fetch();
$stmt->close();
?>


<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
</head>

<body>
    <h2>Welcome,
        <?php echo $email; ?>!
    </h2>
    <p>This is your dashboard content. You are logged in and verified.</p>
    <p><a href="logout.php">Logout</a></p>

    <p>Your account was registered at:
        <?= $registered_time; ?>
    </p>

</body>

</html>