<?php
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="res.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="payments.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Charm&family=Pacifico&family=Quicksand:wght@300;400;500;700&family=Raleway:ital,wght@0,100;0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Charm&family=Quicksand:wght@500&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Charm:wght@400;700&family=Quicksand:wght@200;300;400;500;700&family=Raleway:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php"><img src="../images/logo2.png"></a>
        <ul>
            <li><a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
            <li><a href="manage_cars.php"><i class="fa-solid fa-car"></i> Manage Cars</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a></li>
            <li><a href="reservations.php" class="active"><i class="fa-solid fa-calendar-check"></i> Reservations</a></li>
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
       <!-- Customer Details Section -->
<div class="manage-reservation">
    <h1>Manage Customers</h1>
    <form method="post" action="reservations.php">
        <div class="filter-label">Filter By:</div>

        <div class="filter-container">
            <!-- Reservation and Customer ID Filter -->
            <div class="reservation-id-filter">
                <div class="filter-heading">Reservation Id</div>
                <div class="reservation-container">
                    <input class="reservation-select" name="reservation_id" placeholder="Reservation ID" type="number" min="1" max="1000">
                </div>
            </div>
            <div class="reservation-filter">
                <div class="filter-heading">Customer Id</div>
                <div class="reservation-container">
                    <input class="reservation-select" name="customer_id" placeholder="Customer ID" type="number" min="1" max="1000">
                </div>
            </div>
            <button class="search-btn" type="submit">Search</button>
        </div>
    </form>
</div>

            
            <!-- get queries from database to show customers data in a tableeee-->
            
            <?php
           
            
           $query = "SELECT ReservationID, CustomerID, CarID, ReservationDate, PickupDate, ReturnDate FROM reservations";

           $conditions = [];
           
           // Check if the request method is POST
           if ($_SERVER['REQUEST_METHOD'] === 'POST') {
               // Get input values
               $reservation_id = !empty($_POST['reservation_id']) ? intval($_POST['reservation_id']) : null;
               $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
           
               // Add conditions based on input
               if ($reservation_id) {
                   $conditions[] = "ReservationID = $reservation_id";
               }
               if ($customer_id) {
                   $conditions[] = "CustomerID = $customer_id";
               }
           
               // Append conditions to the query
               if (count($conditions) > 0) {
                   $query .= " WHERE " . implode(" AND ", $conditions);
               }
           }
           
           // Execute query
           $result = mysqli_query($conn, $query);
           
           if ($result && mysqli_num_rows($result) > 0) {
               // Display results in a table
               echo "<table border='1'>
                       <thead>
                           <tr>
                               <th>Reservation ID</th>
                               <th>Customer ID</th>
                               <th>Car ID</th>
                               <th>Reservation Date</th>
                               <th>Pickup Date</th>
                               <th>Return Date</th>
                           </tr>
                       </thead>
                       <tbody>";
           
               while ($row = mysqli_fetch_assoc($result)) {
                   echo "<tr>
                           <td>{$row['ReservationID']}</td>
                           <td>{$row['CustomerID']}</td>
                           <td>{$row['CarID']}</td>
                           <td>{$row['ReservationDate']}</td>
                           <td>{$row['PickupDate']}</td>
                           <td>{$row['ReturnDate']}</td>
                       </tr>";
               }
           
               echo "</tbody>
                   </table>";
           } else {
               echo "<p>No results found.</p>";
           }
           
           // Close connection
           mysqli_close($conn);
           ?>
        
       
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
