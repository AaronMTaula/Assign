<?php
/*
  File: booking.php
  Author: Aaron Taula - 15905800
  Description: Receives booking data from booking.js, generates a booking
               reference number, saves it to the database, and returns
               a confirmation as JSON.

  Functions:
    generateBRN($conn) - works out the next booking reference number
*/

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Load database credentials from settings.php
$settingsPath = __DIR__ . '/../../files/settings.php';
if (!file_exists($settingsPath)) {
    echo json_encode(["success" => false, "message" => "settings.php not found"]);
    exit;
}
require_once($settingsPath);

// Connect using the variable names defined in settings.php
$conn = mysqli_connect($host, $user, $pswd, $dbnm);
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// generateBRN - finds the last booking reference number and adds 1
// Returns a string like BRN00001, BRN00002 etc.
function generateBRN($conn) {
    $result = mysqli_query($conn, "SELECT booking_ref FROM bookings ORDER BY id DESC LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        // Get the number part (after "BRN") and add 1
        $lastNumber = (int) substr($row['booking_ref'], 3);
        $nextNumber = $lastNumber + 1;
    } else {
        // No bookings yet, start at 1
        $nextNumber = 1;
    }
    // Pad to 5 digits e.g. 1 becomes 00001
    return 'BRN' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

// Read the JSON body sent from booking.js
$input = json_decode(file_get_contents('php://input'), true);

// Get each field from the input
$cname   = trim($input['cname']   ?? '');
$phone   = trim($input['phone']   ?? '');
$unumber = trim($input['unumber'] ?? '');
$snumber = trim($input['snumber'] ?? '');
$stname  = trim($input['stname']  ?? '');
$sbname  = trim($input['sbname']  ?? '');
$dsbname = trim($input['dsbname'] ?? '');
$date    = trim($input['date']    ?? '');
$time    = trim($input['time']    ?? '');

// Check required fields are present
if (empty($cname) || empty($phone) || empty($snumber) || empty($stname) || empty($date) || empty($time)) {
    echo json_encode(["success" => false, "message" => "Required fields are missing."]);
    exit;
}

// Generate the next booking reference number
$brn = generateBRN($conn);

// Insert the booking into the database
$stmt = mysqli_prepare($conn,
    "INSERT INTO bookings (booking_ref, cname, phone, unumber, snumber, stname, sbname, dsbname, pickup_date, pickup_time, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unassigned', NOW())"
);

mysqli_stmt_bind_param($stmt, 'ssssssssss', $brn, $cname, $phone, $unumber, $snumber, $stname, $sbname, $dsbname, $date, $time);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success"     => true,
        "brn"         => $brn,
        "pickup_date" => $date,
        "pickup_time" => $time
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save booking."]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
