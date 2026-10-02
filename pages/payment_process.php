<?php
require_once __DIR__ . "/../db.php";

$booking_id = (int)($_GET['booking_id'] ?? 0);
$message = "";
$success = "";
$methods = ["CASH", "GCASH", "CARD", "BANK TRANSFER"];

function load_booking($conn, $booking_id) {
  $stmt = mysqli_prepare($conn, "
    SELECT b.booking_id, b.total_cost, b.status,
           (SELECT IFNULL(SUM(p.amount_paid), 0) FROM payments p WHERE p.booking_id = b.booking_id) AS paid
    FROM bookings b
    WHERE b.booking_id = ?
  ");
  mysqli_stmt_bind_param($stmt, "i", $booking_id);
  mysqli_stmt_execute($stmt);
  $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);
  return $row;
}

$booking = load_booking($conn, $booking_id);

if (!$booking) {
  header("Location: bookings_list.php");
  exit;
}

if (isset($_POST['pay'])) {
  $amount = trim($_POST['amount_paid'] ?? '');
  $method = $_POST['method'] ?? '';

  if (!is_numeric($amount) || (float)$amount <= 0) {
    $message = "Enter a payment amount greater than 0.";
  } elseif (!in_array($method, $methods, true)) {
    $message = "Select a valid payment method.";
  } else {
    $amount = round((float)$amount, 2);

    mysqli_begin_transaction($conn);

    // Lock the booking row so two payments can't be processed at the same time
    $lock = mysqli_prepare($conn, "SELECT total_cost FROM bookings WHERE booking_id = ? FOR UPDATE");
    mysqli_stmt_bind_param($lock, "i", $booking_id);
    mysqli_stmt_execute($lock);
    $lockRow = mysqli_fetch_assoc(mysqli_stmt_get_result($lock));
    mysqli_stmt_close($lock);

    $sum = mysqli_prepare($conn, "SELECT IFNULL(SUM(amount_paid), 0) AS paid FROM payments WHERE booking_id = ?");
    mysqli_stmt_bind_param($sum, "i", $booking_id);
    mysqli_stmt_execute($sum);
    $sumRow = mysqli_fetch_assoc(mysqli_stmt_get_result($sum));
    mysqli_stmt_close($sum);

    $total   = round((float)$lockRow['total_cost'], 2);
    $paid    = round((float)$sumRow['paid'], 2);
    $balance = round($total - $paid, 2);

    if ($balance <= 0) {
      mysqli_rollback($conn);
      $message = "This booking is already fully paid.";
    } elseif ($amount > $balance) {
      mysqli_rollback($conn);
      $message = "Amount exceeds the remaining balance of ₱" . number_format($balance, 2) . ".";
    } else {
      $ins = mysqli_prepare($conn, "INSERT INTO payments (booking_id, amount_paid, method) VALUES (?, ?, ?)");
      mysqli_stmt_bind_param($ins, "ids", $booking_id, $amount, $method);
      $ok = mysqli_stmt_execute($ins);
      mysqli_stmt_close($ins);

      $new_status = (round($paid + $amount, 2) >= $total) ? "PAID" : "PARTIAL";

      $upd = mysqli_prepare($conn, "UPDATE bookings SET status = ? WHERE booking_id = ?");
      mysqli_stmt_bind_param($upd, "si", $new_status, $booking_id);
      $ok2 = mysqli_stmt_execute($upd);
      mysqli_stmt_close($upd);

      if ($ok && $ok2) {
        mysqli_commit($conn);
        $success = "Payment of ₱" . number_format($amount, 2) . " recorded. Status: $new_status.";
      } else {
        mysqli_rollback($conn);
        $message = "Unable to process payment.";
      }
    }
  }

  // Reload so the page shows the updated totals
  $booking = load_booking($conn, $booking_id);
}

$total_cost = (float)$booking['total_cost'];
$total_paid = (float)$booking['paid'];
$balance    = round($total_cost - $total_paid, 2);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Process Payment</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Process Payment (Booking #<?php echo (int)$booking['booking_id']; ?>)</h2>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
<p style="color:green;"><?php echo htmlspecialchars($success); ?></p>

<p>Total Cost: <b>₱<?php echo number_format($total_cost, 2); ?></b></p>
<p>Total Paid: <b>₱<?php echo number_format($total_paid, 2); ?></b></p>
<p>Balance: <b>₱<?php echo number_format(max($balance, 0), 2); ?></b></p>

<?php if ($balance > 0) { ?>
  <form method="post">
    <label>Amount Paid</label><br>
    <input type="number" name="amount_paid" step="0.01" min="0.01" max="<?php echo $balance; ?>" value="<?php echo $balance; ?>"><br><br>

    <label>Payment Method</label><br>
    <select name="method">
      <?php foreach ($methods as $m) { ?>
        <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
      <?php } ?>
    </select><br><br>

    <button type="submit" name="pay">Submit Payment</button>
  </form>
<?php } else { ?>
  <p><b>This booking is fully paid.</b></p>
<?php } ?>

<p><a href="bookings_list.php">Back to Bookings</a></p>

</body>
</html>