<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$model = isset($_GET['model']) ? $_GET['model'] : '';
$yearFrom = isset($_GET['yearFrom']) ? $_GET['yearFrom'] : 1990;
$yearTo = isset($_GET['yearTo']) ? $_GET['yearTo'] : 2024;
$priceFrom = isset($_GET['priceFrom']) ? $_GET['priceFrom'] : 0;
$priceTo = isset($_GET['priceTo']) ? $_GET['priceTo'] : PHP_INT_MAX;

$sql = "SELECT * FROM cars WHERE Status = 'Active' ";

if ($model != '') {
    $sql .= " AND Model = '" . $conn->real_escape_string($model) . "'";
}
if (!empty($yearFrom) && !empty($yearTo)) {
    $sql .= " AND Year BETWEEN $yearFrom AND $yearTo";
}

if ($priceFrom && $priceTo) {
    $sql .= " AND Price BETWEEN $priceFrom AND $priceTo";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="car-card">
    <img src="' . $row['ImagePath'] . '" alt="' . $row['Model'] . '">
    <div class="card-content">
        <div class="car-name">' . $row['Model'] . '</div>
        <div class="car-year">Year: ' . $row['Year'] . '</div>
        <div class="car-details">
            <span>Plate ID: ' . $row['PlateID'] . '</span>
            <span>Office ID: ' . $row['OfficeID'] . '</span>
        </div>
        <div class="price">$' . $row['Price'] . '/month</div>
        <button class="rent-button" onclick="rentNow(' . $row['CarID'] . ')">Rent Now</button>
    </div>
</div>';

    }
} else {
    echo '<p>No cars match your criteria.</p>';
}

$conn->close();
