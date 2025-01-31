<?php
session_start();

// ===== DATABASE CONNECTION =====
function connectToDatabase() {
        // Connect to MySQL database
        $database = new PDO(
            "mysql:host=localhost;dbname=car_rental_system", 
            "root", 
            ""
        );       
        return $database;
        
}

// Connect to database
$database = connectToDatabase();
if (!$database) {
    die("Could not connect to database");
}

// Count total items in a table with optional condition
function countItems($database, $tableName, $whereClause = '') {
    try {
        $query = "SELECT COUNT(*) FROM $tableName $whereClause";
        $result = $database->query($query);
        return $result->fetchColumn();
    } catch (PDOException $error) {
        error_log("Error counting items: " . $error->getMessage());
        return 0;
    }
}

// Calculate total revenue for current month
function getThisMonthRevenue($database) {
    try {
        $query = "SELECT COALESCE(SUM(Amount), 0) as total 
                 FROM payments 
                 WHERE MONTH(PaymentDate) = MONTH(CURRENT_DATE()) 
                 AND YEAR(PaymentDate) = YEAR(CURRENT_DATE())";
        
        $result = $database->query($query);
        return $result->fetchColumn();
    } catch (PDOException $error) {
        return 0;
    }
}

// ===== GET DASHBOARD DATA =====

// Get basic statistics
$dashboardStats = [
    'totalCustomers' => countItems($database, 'customers'),
    'carsAvailable' => countItems($database, 'cars', "WHERE Status = 'Active'"),
    'activeBookings' => countItems($database, 'reservations', "WHERE Status IN ('Reserved', 'Picked Up')"),
    'revenueThisMonth' => getThisMonthRevenue($database)
];

// Get 5 most recent reservations
$recentBookings = [];
try {
    $query = "SELECT 
        r.ReservationID,
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        cars.Model as CarModel,
        r.PickupDate,
        r.ReturnDate,
        r.Status
    FROM reservations r
    JOIN customers c ON r.CustomerID = c.CustomerID
    JOIN cars ON r.CarID = cars.CarID
    ORDER BY r.ReservationDate DESC
    LIMIT 5";
    
    $result = $database->query($query);
    $recentBookings = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    // If there's an error, recentBookings will remain an empty array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="payments.css">
    <link rel="icon" href="../images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Charm&family=Quicksand:wght@500&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Charm:wght@400;700&family=Quicksand:wght@200;300;400;500;700&family=Raleway:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php"><img src="../images/logo2.png"></a>
        <ul>
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a></li>
            <li><a href="manage_cars.php"><i class="fa-solid fa-car"></i> Manage Cars</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a></li>
            <li><a href="reservations.php"><i class="fa-solid fa-calendar-check"></i> Reservations</a></li>
            <li><a href="payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a></li>
            <li><a href="../main/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Log out</a></li>
        </ul>        
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <button class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
            <input type="text" class="search-bar" placeholder="Search...">
            <div class="profile">
                <i class="fa-solid fa-circle-user fa-2x"></i>
                <span style="font-weight: 600;"><?php echo $_SESSION['Name']; ?></span> 
            </div>
        </header>

        <!-- Dashboard Cards -->
        <div class="cards">
            <div class="card">
                <div class="card-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="card-text">
                    <p><?php echo number_format($dashboardStats['totalCustomers']); ?></p>
                    <h3>Total Users</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-icon" id="car">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div class="card-text">
                    <p><?php echo number_format($dashboardStats['carsAvailable']); ?></p>
                    <h3>Available Cars</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="card-text">
                    <p><?php echo number_format($dashboardStats['activeBookings']); ?></p>
                    <h3>Active Reservations</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="card-text">
                    <p>$<?php echo number_format($dashboardStats['revenueThisMonth'], 2); ?></p>
                    <h3>This Month</h3>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="data-table">
            <h2>Recent Reservations</h2>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Car Model</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['CustomerName']); ?></td>
                        <td><?php echo htmlspecialchars($booking['CarModel']); ?></td>
                        <td><?php echo htmlspecialchars($booking['PickupDate']); ?></td>
                        <td><?php echo htmlspecialchars($booking['ReturnDate']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($booking['Status']); ?>">
                                <?php echo htmlspecialchars($booking['Status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        }
    </script>
</body>
</html>