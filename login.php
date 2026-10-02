<?php
$no_auth = true;
include "db.php";

if (isset($_SESSION['username'])) {
  header("Location: index.php");
  exit;
}

$message = "";

if (isset($_POST['login'])) {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username === "admin" && $password === "admin") {
    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    header("Location: index.php");
    exit;
  }

  $message = "Invalid username or password.";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
</head>
<body>

<h2>Login</h2>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>

<form method="post">
  <label>Username</label><br>
  <input type="text" name="username"><br><br>

  <label>Password</label><br>
  <input type="password" name="password"><br><br>

  <button type="submit" name="login">Login</button>
</form>

</body>
</html>