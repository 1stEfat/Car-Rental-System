<?php
header("Content-Type: application/json");
include 'connection.php';

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

$model = $data['model'] ?? '';
$yearFrom = $data['yearFrom'] ?? '';
$yearTo = $data['yearTo'] ?? '';
$status = $data['status'] ?? '';

// Base query
$query = "SELECT Model, Year, PlateID, Status FROM Cars WHERE 1=1";

// Dynamically add filters
if (!empty($model)) {
    $query .= " AND Model = ?";
}
if (!empty($yearFrom)) {
    $query .= " AND Year >= ?";
}
if (!empty($yearTo)) {
    $query .= " AND Year <= ?";
}
if (!empty($status)) {
    $query .= " AND Status = ?";
}

$stmt = $conn->prepare($query);

// Bind parameters
$params = [];
if (!empty($model)) $params[] = $model;
if (!empty($yearFrom)) $params[] = $yearFrom;
if (!empty($yearTo)) $params[] = $yearTo;
if (!empty($status)) $params[] = $status;

// Execute the query
$stmt->execute($params);
$result = $stmt->get_result();

// Fetch results
$cars = [];
while ($row = $result->fetch_assoc()) {
    $cars[] = $row;
}

// Respond with JSON
echo json_encode([
    "success" => true,
    "cars" => $cars,
    "message" => empty($cars) ? "No cars found." : "Cars retrieved successfully."
]);
?>
