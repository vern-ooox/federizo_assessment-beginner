<?php
include "../db.php";

$id = $_GET['id'] ?? 0;
$id = (int)$id;

$message = "";
$client = null;

$get = mysqli_query($conn, "SELECT * FROM clients WHERE client_id = $id");
$client = mysqli_fetch_assoc($get);

if (!$client) {
  header("Location: clients_list.php");
  exit;
}

if (isset($_POST['update'])) {
  $full_name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');

  if ($full_name === "" || $email === "") {
    $message = "Name and Email are required!";
  } else {
    $full_name = mysqli_real_escape_string($conn, $full_name);
    $email = mysqli_real_escape_string($conn, $email);
    $phone = mysqli_real_escape_string($conn, $phone);
    $address = mysqli_real_escape_string($conn, $address);

    $sql = "UPDATE clients
            SET full_name='$full_name', email='$email', phone='$phone', address='$address'
            WHERE client_id=$id";

    if (mysqli_query($conn, $sql)) {
      header("Location: clients_list.php");
      exit;
    }

    $message = "Unable to update client.";
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Client</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Edit Client</h2>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>

<form method="post">
  <label>Full Name*</label><br>
  <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? $client['full_name']); ?>"><br><br>

  <label>Email*</label><br>
  <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $client['email']); ?>"><br><br>

  <label>Phone</label><br>
  <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? $client['phone']); ?>"><br><br>

  <label>Address</label><br>
  <input type="text" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? $client['address']); ?>"><br><br>

  <button type="submit" name="update">Update</button>
</form>
</body>
</html>