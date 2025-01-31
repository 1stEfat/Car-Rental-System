<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
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
            <li><a href="reservations.php"><i class="fa-solid fa-calendar-check"></i> Reservations</a></li>
            <li><a href="payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a></li>
            <li><a href="reports.php" class="active"><i class="fa-solid fa-file-alt"></i> Reports</a></li>
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
