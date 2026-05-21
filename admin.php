<?php
/*
  File: admin.php
  Author: Aaron Taula - 15905800
  Description: Handles four actions for the admin page:
               - action=search:   finds bookings by BRN or upcoming unassigned ones
               - action=current:  returns all current and upcoming bookings
               - action=past:     returns all past bookings
               - action=assign:   updates a booking status to assigned

  Functions:
    handleSearch($conn, $brn)  - searches by BRN or returns bookings within 2 hours
    handleCurrent($conn)       - returns all bookings from now onwards
    handlePast($conn)          - returns all bookings with a past pickup time
    handleAssign($conn, $brn)  - updates a booking status to assigned
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

// Connect to the database
$conn = mysqli_connect($host, $user, $pswd, $dbnm);
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// handleSearch - searches by BRN or returns unassigned bookings within 2 hours
function handleSearch($conn, $brn) {
    $bookings = [];

    if ($brn !== '') {
        // Search for a specific booking reference number
        $stmt = mysqli_prepare($conn,
            "SELECT booking_ref, cname, phone, sbname, dsbname, pickup_date, pickup_time, status
             FROM bookings WHERE booking_ref = ?"
        );
        mysqli_stmt_bind_param($stmt, 's', $brn);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
        mysqli_stmt_close($stmt);

    } else {
        // Find unassigned bookings with pickup time within the next 2 hours
        $stmt = mysqli_prepare($conn,
            "SELECT booking_ref, cname, phone, sbname, dsbname, pickup_date, pickup_time, status
             FROM bookings
             WHERE status = 'unassigned'
             AND TIMESTAMP(pickup_date, pickup_time) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 HOUR)
             ORDER BY pickup_date ASC, pickup_time ASC"
        );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    echo json_encode(["success" => true, "bookings" => $bookings]);
}

// handleCurrent - returns all bookings with a pickup time from now onwards
function handleCurrent($conn) {
    $bookings = [];

    $stmt = mysqli_prepare($conn,
        "SELECT booking_ref, cname, phone, sbname, dsbname, pickup_date, pickup_time, status
         FROM bookings
         WHERE TIMESTAMP(pickup_date, pickup_time) >= NOW()
         ORDER BY pickup_date ASC, pickup_time ASC"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode(["success" => true, "bookings" => $bookings]);
}

// handlePast - returns all bookings with a pickup time that has already passed
function handlePast($conn) {
    $bookings = [];

    $stmt = mysqli_prepare($conn,
        "SELECT booking_ref, cname, phone, sbname, dsbname, pickup_date, pickup_time, status
         FROM bookings
         WHERE TIMESTAMP(pickup_date, pickup_time) < NOW()
         ORDER BY pickup_date DESC, pickup_time DESC"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode(["success" => true, "bookings" => $bookings]);
}

// handleAssign - updates the status of a booking from unassigned to assigned
function handleAssign($conn, $brn) {
    if (empty($brn)) {
        echo json_encode(["success" => false, "message" => "No booking reference provided."]);
        exit;
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE bookings SET status = 'assigned' WHERE booking_ref = ? AND status = 'unassigned'"
    );
    mysqli_stmt_bind_param($stmt, 's', $brn);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "brn" => $brn]);
    } else {
        echo json_encode(["success" => false, "message" => "Booking not found or already assigned."]);
    }

    mysqli_stmt_close($stmt);
}

// Route to the correct function based on the action parameter
$action = $_GET['action'] ?? '';

if ($action === 'search') {
    $brn = trim($_GET['bsearch'] ?? '');
    handleSearch($conn, $brn);

} elseif ($action === 'current') {
    handleCurrent($conn);

} elseif ($action === 'past') {
    handlePast($conn);

} elseif ($action === 'assign') {
    $brn = trim($_GET['brn'] ?? '');
    handleAssign($conn, $brn);

} else {
    echo json_encode(["success" => false, "message" => "Unknown action."]);
}

mysqli_close($conn);
?>
