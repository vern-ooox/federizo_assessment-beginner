<?php
include "../db.php";

$message = "";
$full_name = "";
$email = "";
$phone = "";
$address = "";

if (isset($_POST['save'])) {
  $full_name = trim($_POST['full_name'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $phone     = trim($_POST['phone'] ?? '');
  $address   = trim($_POST['address'] ?? '');

  if ($full_name === "" || $email === "") {
    $message = "Name and Email are required!";
  } else {
    $full_name = mysqli_real_escape_string($conn, $full_name);
    $email = mysqli_real_escape_string($conn, $email);
    $phone = mysqli_real_escape_string($conn, $phone);
    $address = mysqli_real_escape_string($conn, $address);

    $sql = "INSERT INTO clients (full_name, email, phone, address)
            VALUES ('$full_name', '$email', '$phone', '$address')";

    if (mysqli_query($conn, $sql)) {
      header("Location: clients_list.php");
      exit;
    }

    $message = "Unable to save client.";
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Client</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Add Client</h2>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>

<form method="post">
  <label>Full Name*</label><br>
  <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>"><br><br>

  <label>Email*</label><br>
  <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"><br><br>

  <label>Phone</label><br>
  <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>"><br><br>

  <label>Address</label><br>
  <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>"><br><br>

  <button type="submit" name="save">Save</button>
</form>
</body>
</html>