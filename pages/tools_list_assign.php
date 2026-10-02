<?php
include "../db.php";

$message = "";
$success = "";

if (isset($_POST['assign'])) {
  $booking_id = (int)($_POST['booking_id'] ?? 0);
  $tool_id    = (int)($_POST['tool_id'] ?? 0);
  $qty_used   = (int)($_POST['qty_used'] ?? 0);

  if ($booking_id <= 0 || $tool_id <= 0 || $qty_used <= 0) {
    $message = "Booking ID, tool and quantity (at least 1) are required.";
  } else {
    // Check that the booking exists
    $chk = mysqli_prepare($conn, "SELECT booking_id FROM bookings WHERE booking_id = ?");
    mysqli_stmt_bind_param($chk, "i", $booking_id);
    mysqli_stmt_execute($chk);
    mysqli_stmt_store_result($chk);
    $booking_exists = mysqli_stmt_num_rows($chk) > 0;
    mysqli_stmt_close($chk);

    if (!$booking_exists) {
      $message = "Booking ID $booking_id does not exist.";
    } else {
      mysqli_begin_transaction($conn);

      // Only deducts if enough stock is available
      $upd = mysqli_prepare($conn,
        "UPDATE tools SET quantity_available = quantity_available - ?
         WHERE tool_id = ? AND quantity_available >= ?");
      mysqli_stmt_bind_param($upd, "iii", $qty_used, $tool_id, $qty_used);
      mysqli_stmt_execute($upd);
      $deducted = mysqli_stmt_affected_rows($upd) === 1;
      mysqli_stmt_close($upd);

      if (!$deducted) {
        mysqli_rollback($conn);
        $message = "Not enough available quantity for that tool.";
      } else {
        $ins = mysqli_prepare($conn,
          "INSERT INTO booking_tools (booking_id, tool_id, qty_used) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($ins, "iii", $booking_id, $tool_id, $qty_used);

        if (mysqli_stmt_execute($ins)) {
          mysqli_commit($conn);
          $success = "Tool assigned to booking #$booking_id.";
        } else {
          mysqli_rollback($conn);
          $message = "Unable to assign tool.";
        }
        mysqli_stmt_close($ins);
      }
    }
  }
}

$tools_table = mysqli_query($conn, "SELECT * FROM tools ORDER BY tool_name ASC");
$tools_drop  = mysqli_query($conn, "SELECT * FROM tools ORDER BY tool_name ASC");
$assigned    = mysqli_query($conn, "
  SELECT bt.booking_tool_id, bt.booking_id, t.tool_name, bt.qty_used, bt.created_at
  FROM booking_tools bt
  JOIN tools t ON bt.tool_id = t.tool_id
  ORDER BY bt.booking_tool_id DESC
");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tools Inventory</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Tools Inventory</h2>

<table border="1" cellpadding="8">
  <tr>
    <th>ID</th><th>Tool</th><th>Total</th><th>Available</th>
  </tr>
  <?php if (mysqli_num_rows($tools_table) > 0) { ?>
    <?php while ($t = mysqli_fetch_assoc($tools_table)) { ?>
      <tr>
        <td><?php echo (int)$t['tool_id']; ?></td>
        <td><?php echo htmlspecialchars($t['tool_name']); ?></td>
        <td><?php echo (int)$t['quantity_total']; ?></td>
        <td><?php echo (int)$t['quantity_available']; ?></td>
      </tr>
    <?php } ?>
  <?php } else { ?>
    <tr><td colspan="4">No tools found.</td></tr>
  <?php } ?>
</table>

<h3>Assign Tool to Booking</h3>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
<p style="color:green;"><?php echo htmlspecialchars($success); ?></p>

<form method="post">
  <label>Booking ID</label><br>
  <input type="number" name="booking_id" min="1"><br><br>

  <label>Tool</label><br>
  <select name="tool_id">
    <?php while ($t = mysqli_fetch_assoc($tools_drop)) { ?>
      <option value="<?php echo (int)$t['tool_id']; ?>">
        <?php echo htmlspecialchars($t['tool_name']); ?> (available: <?php echo (int)$t['quantity_available']; ?>)
      </option>
    <?php } ?>
  </select><br><br>

  <label>Quantity Used</label><br>
  <input type="number" name="qty_used" min="1" value="1"><br><br>

  <button type="submit" name="assign">Assign</button>
</form>

<h3>Assigned Tools</h3>
<table border="1" cellpadding="8">
  <tr>
    <th>ID</th><th>Booking ID</th><th>Tool</th><th>Qty Used</th><th>Date</th>
  </tr>
  <?php if (mysqli_num_rows($assigned) > 0) { ?>
    <?php while ($a = mysqli_fetch_assoc($assigned)) { ?>
      <tr>
        <td><?php echo (int)$a['booking_tool_id']; ?></td>
        <td><?php echo (int)$a['booking_id']; ?></td>
        <td><?php echo htmlspecialchars($a['tool_name']); ?></td>
        <td><?php echo (int)$a['qty_used']; ?></td>
        <td><?php echo htmlspecialchars($a['created_at']); ?></td>
      </tr>
    <?php } ?>
  <?php } else { ?>
    <tr><td colspan="5">No tools assigned yet.</td></tr>
  <?php } ?>
</table>

</body>
</html>