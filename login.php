<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Change these to your credentials
    $admin_user = "reda";
    $admin_pass = "12345";

    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin'] = $username;
        header("Location: ViewAppointments.php");
        exit();
    } else {
        $error = "❌ Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; text-align: center; margin-top: 100px; }
        form { display: inline-block; background: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #aaa; }
        input { padding: 10px; margin: 5px; width: 200px; }
        button { padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>🔑 Admin Login</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
