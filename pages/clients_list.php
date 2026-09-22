<?php
include "../db.php";
$result = mysqli_query($conn, "SELECT * FROM clients ORDER BY client_id DESC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Clients</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Clients</h2>
<p><a href="clients_add.php">+ Add Client</a></p>

<table border="1" cellpadding="8">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Action</th>
  </tr>

  <?php if (mysqli_num_rows($result) > 0) { ?>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?php echo (int)$row['client_id']; ?></td>
        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
        <td>
          <a href="clients_edit.php?id=<?php echo (int)$row['client_id']; ?>">Edit</a>
        </td>
      </tr>
    <?php } ?>
  <?php } else { ?>
    <tr>
      <td colspan="5">No clients found.</td>
    </tr>
  <?php } ?>
</table>
</body>
</html>