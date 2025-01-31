<?php
include 'connection.php';




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage customers</title>
    <link rel="stylesheet" href="customers.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="payments.css">
    
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
            <li><a href="customers.php" class="active"><i class="fa-solid fa-users"></i> Customers</a></li>
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


        <!-- Customer details Section -->
        <div class="manage-customers">
            <h1>Manage Customers</h1>
                <form method="post" action="customers.php">
            <div class="filter-label">Filter By:</div>

            <div class="filter-container">
            
                <!-- customer id filter -->
                <div class="customer-id-filter">
                    <div class="filter-heading">Customer Id</div>
                    <div class="customer-filter">
                    
                    <div class="customer-container">
                        <input class="customer-select" name="customer_id" placeholder="id" type="number" min="1" max="1000">
                        <button  class="search-btn" type="submit">Search</button>
                        
                    </div>
                </div></form>
                </div>        

               
            
                
            </div>   
            
            <!-- get queries from database to show customers data in a tableeee-->
            
            <?php
           
            
            $query = "SELECT CustomerID, FirstName, LastName, PhoneNumber, Address FROM Customers";
            



         
            
        
               
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customer_id = $_POST['customer_id'];

        if (!empty($customer_id)) {
            
            $query .= " WHERE CustomerID = " . intval($customer_id);
        }
    }

    
    $result = mysqli_query($conn, $query);

    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1'>
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Phone Number</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['CustomerID']}</td>
                    <td>{$row['FirstName']}</td>
                    <td>{$row['LastName']}</td>
                    <td>{$row['PhoneNumber']}</td>
                    <td>{$row['Address']}</td>
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
