<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
        exit;
    }

    $reservation_id    = $_POST['reservation_id'] ?? 0;
    $customer_name     = $_POST['customer_name'] ?? '';
    $email            = $_POST['email'] ?? '';
    $phone            = $_POST['phone'] ?? '';
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $guests           = $_POST['guests'] ?? 1;
    $newStatus        = $_POST['status'] ?? 'Pending';

    // 1) Fetch old record
    $oldQuery = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
    $oldQuery->bind_param("i", $reservation_id);
    $oldQuery->execute();
    $oldResult = $oldQuery->get_result();
    $oldStatus = '';
    if ($oldRow = $oldResult->fetch_assoc()) {
        $oldStatus = $oldRow['status'];
    }
    $oldQuery->close();

    // 2) Prepare an UPDATE
    $stmt = $conn->prepare("
        UPDATE reservations
        SET 
            customer_name     = ?,
            email            = ?,
            phone            = ?,
            reservation_date = ?,
            reservation_time = ?,
            guests           = ?,
            status           = ?
        WHERE reservation_id  = ?
    ");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // s, s, s, s, s, i, s, i
    $stmt->bind_param(
        "sssssisi",
        $customer_name,
        $email,
        $phone,
        $reservation_date,
        $reservation_time,
        $guests,
        $newStatus,
        $reservation_id
    );

    if ($stmt->execute()) {
        // 3) If status changed to Confirmed or Cancelled, send email
        if ($oldStatus !== $newStatus && ($newStatus === 'Confirmed' || $newStatus === 'Cancelled')) {
            // Build your mail
            $subject = "Your Reservation is now $newStatus";
            $body    = "Hello $customer_name,\n\n"
                     . "Your reservation on $reservation_date at $reservation_time "
                     . "has been marked as $newStatus.\n"
                     . "Thank you!";
            
            // For HTML emails, you'd set appropriate headers
            $headers = "From: reservations@yourdomain.com\r\n"
                     . "Reply-To: reservations@yourdomain.com\r\n"
                     . "Content-type: text/plain; charset=UTF-8\r\n";

            // Attempt to send
            if (!mail($email, $subject, $body, $headers)) {
                // Log or handle mail error
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Reservation updated successfully.']);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Execution failed: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
}
