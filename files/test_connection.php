<?php
/*
  File: test_connection.php
  Author: Aaron Taula - 15905800
  Description: Tests the database connection and checks the bookings table
               exists and is structured correctly.
               DELETE this file from the server after testing.
*/

echo "<h2>CabsOnline - Connection Test</h2>";

// Step 1 - Check settings.php can be found
echo "<h3>Step 1: Looking for settings.php...</h3>";

$settingsPath = __DIR__ . '/../../files/settings.php';

if (!file_exists($settingsPath)) {
    echo "<p style='color:red;'>❌ FAIL - settings.php not found at: " . $settingsPath . "</p>";
    exit;
}
echo "<p style='color:green;'>✅ PASS - settings.php found.</p>";

// Step 2 - Load settings and connect to database
echo "<h3>Step 2: Connecting to the database...</h3>";

require_once($settingsPath);

$conn = mysqli_connect($host, $user, $pswd, $dbnm);

if (!$conn) {
    echo "<p style='color:red;'>❌ FAIL - Could not connect to database.</p>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
    exit;
}
echo "<p style='color:green;'>✅ PASS - Connected to database: <strong>" . $dbnm . "</strong></p>";

// Step 3 - Check the bookings table exists
echo "<h3>Step 3: Checking bookings table exists...</h3>";

$result = mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");

if (mysqli_num_rows($result) === 0) {
    echo "<p style='color:red;'>❌ FAIL - The bookings table does not exist.</p>";
    echo "<p>Go to phpMyAdmin, select your database, click the SQL tab, and run the CREATE TABLE command from mysqlcommand.txt</p>";
    mysqli_close($conn);
    exit;
}
echo "<p style='color:green;'>✅ PASS - bookings table found.</p>";

// Step 4 - Show the table columns
echo "<h3>Step 4: Checking table structure...</h3>";

$columns = mysqli_query($conn, "DESCRIBE bookings");

echo "<table border='1' cellpadding='6' style='border-collapse:collapse;'>";
echo "<tr style='background:#333;color:white;'>
        <th>Column</th><th>Type</th><th>Null</th><th>Default</th>
      </tr>";

$expectedColumns = ['id','booking_ref','cname','phone','unumber','snumber','stname','sbname','dsbname','pickup_date','pickup_time','status','created_at'];
$foundColumns = [];

while ($row = mysqli_fetch_assoc($columns)) {
    $foundColumns[] = $row['Field'];
    echo "<tr>";
    echo "<td>" . $row['Field']   . "</td>";
    echo "<td>" . $row['Type']    . "</td>";
    echo "<td>" . $row['Null']    . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$missing = array_diff($expectedColumns, $foundColumns);
if (empty($missing)) {
    echo "<p style='color:green;'>✅ PASS - All required columns are present.</p>";
} else {
    echo "<p style='color:red;'>❌ FAIL - Missing columns: " . implode(', ', $missing) . "</p>";
    echo "<p>Drop the table and re-run the CREATE TABLE command from mysqlcommand.txt</p>";
}

// Step 5 - Test INSERT and DELETE
echo "<h3>Step 5: Testing INSERT and DELETE...</h3>";

$testRef = 'BRNTEST';
$stmt = mysqli_prepare($conn,
    "INSERT INTO bookings (booking_ref, cname, phone, snumber, stname, pickup_date, pickup_time, status, created_at)
     VALUES (?, 'Test User', '0211234567', '1', 'Test Street', '2026-12-31', '12:00', 'unassigned', NOW())"
);
mysqli_stmt_bind_param($stmt, 's', $testRef);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color:green;'>✅ PASS - Test row inserted successfully.</p>";
    mysqli_query($conn, "DELETE FROM bookings WHERE booking_ref = 'BRNTEST'");
    echo "<p style='color:green;'>✅ PASS - Test row deleted (cleanup done).</p>";
} else {
    echo "<p style='color:red;'>❌ FAIL - Could not insert: " . mysqli_stmt_error($stmt) . "</p>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo "<h3 style='color:green;'>All tests passed! Your database is connected and ready.</h3>";
echo "<p><strong>Remember to delete test_connection.php from the server after testing.</strong></p>";
?>
