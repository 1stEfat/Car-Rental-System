<?php
session_start();

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'car_rental_system';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$customerID = $_SESSION['CustomerID']; // Get the logged-in user's CustomerID

// Fetch filters from GET parameters
$reservationId = $_GET['reservationId'] ?? '';
$status = $_GET['status'] ?? '';
$fromDate = $_GET['fromDate'] ?? '';
$toDate = $_GET['toDate'] ?? '';

// Build the query
$query = "SELECT * FROM reservations WHERE CustomerID = '$customerID'";

if (!empty($reservationId)) {
    $reservationId = $conn->real_escape_string($reservationId);
    $query .= " AND ReservationID = '$reservationId'";
}
if (!empty($status)) {
    $status = $conn->real_escape_string($status);
    $query .= " AND Status = '$status'";
}
if (!empty($fromDate)) {
    $fromDate = $conn->real_escape_string($fromDate);
    $query .= " AND ReservationDate >= '$fromDate'";
}
if (!empty($toDate)) {
    $toDate = $conn->real_escape_string($toDate);
    $query .= " AND ReservationDate <= '$toDate'";
}

// Execute the query
$result = $conn->query($query);

// Prepare the JSON response
if ($result) {
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    echo json_encode($reservations);
} else {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
}

// Close the connection
$conn->close();
?>
