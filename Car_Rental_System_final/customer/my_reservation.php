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
    die('Connection failed: ' . $conn->connect_error);
}
$customerID = $_SESSION['CustomerID'];
// Fetch reservations from the database
$query = "SELECT * FROM reservations WHERE CustomerID = '$customerID'";
$result = $conn->query($query);


if (!$result) {
    die('Query failed: ' . $conn->error);
}
// Fetch distinct statuses from the database
$statusQuery = "SELECT DISTINCT Status FROM reservations WHERE CustomerID = '$customerID'";
$statusResult = $conn->query($statusQuery);

if (!$statusResult) {
    die('Query failed: ' . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo.png">
    <title>My Reservations</title>
    <link rel="stylesheet" href="../main/style.css">
    <link rel="stylesheet" href="../main/form.css">
    <link rel="stylesheet" href="explore_cars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>   
    <body>

        <header>
        <div class="container">
            <div class="logo">
                <a href="explore_cars.php"><img src="../images/logo1.png"></a>
            </div>
            <nav>
                <ul id="navbar">
                    <li><a href="explore_cars.php" onclick="setActive(this)">Explore Cars</a></li>
                    <li><a href="my_reservation.php" class="active" onclick="setActive(this)">My Reservations</a></li>
                    <li class="dropdown">
    <div class="user-profile" onclick="toggleDropdown()">
        <i class="fa-solid fa-circle-user fa-lg"></i>
        <span class="username"><?php echo $_SESSION['Name']; ?></span>
        <i class="fa-solid fa-caret-down"></i>
    </div>
    <ul class="dropdown-menu">
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
        <li class="logout"><a href="../main/logout.php"><i class="fa-solid fa-right-to-bracket"></i> Log out</a></li>
    </ul>
</li>

                </ul>
            </nav>
        </div>
        </header>
        <div class="manage-reservations">
            <div class="filter-container">
            
                <!-- Reservation Filter -->
                <div class="car-model-filter">
                    <div class="filter-heading">Reservation ID</div>
                    <input class="year-select" name="from" placeholder="Enter Reservation ID" type="number">
                    <select id="carModelSelect" class="car-model-select">
    <option value="">Select Reservation Status</option>
    <?php
    if ($statusResult->num_rows > 0) {
        while ($statusRow = $statusResult->fetch_assoc()) {
            echo "<option value=\"{$statusRow['Status']}\">{$statusRow['Status']}</option>";
        }
    }
    ?>
</select>

                </div>        

                <div class="year-filter">
                    <div class="filter-heading">Year</div>
                    <div class="year-container">
                        <input class="year-select" name="from" placeholder="From" type="date">
                        <input class="year-select" name="to" placeholder="To" type="date">
                    </div>
                </div>
                
            </div>        
            <div class="manage-reservations">
        <table>
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Date</th>
                    <th>Pick up</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop over the results and populate the table rows
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['ReservationID']}</td>
                                <td>{$row['ReservationDate']}</td>
                                <td>{$row['PickupDate']}</td>
                                <td>{$row['Status']}</td>
                              </tr>";
                    }
                } else {
                    echo '<tr><td colspan="4">No reservations found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
        </div>
    
    <script>
        function fetchReservations() {
    const reservationId = document.querySelector('.car-model-filter input').value;
    const status = document.querySelector('#carModelSelect').value;
    const fromDate = document.querySelector('input[name="from"][type="date"]').value;
    const toDate = document.querySelector('input[name="to"][type="date"]').value;

    // Send AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `fetch_reservations.php?reservationId=${reservationId}&status=${status}&fromDate=${fromDate}&toDate=${toDate}`, true);
    xhr.onload = function () {
        if (this.status === 200) {
            const reservations = JSON.parse(this.responseText);
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = '';

            reservations.forEach(reservation => {
                const row = `
                    <tr>
                        <td>${reservation.ReservationID}</td>
                        <td>${reservation.ReservationDate}</td>
                        <td>${reservation.PickupDate}</td>
                        <td>${reservation.Status}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }
    };
    xhr.send();
}

// Attach event listeners to the filters
document.querySelector('.car-model-filter input').addEventListener('input', fetchReservations);
document.querySelector('#carModelSelect').addEventListener('change', fetchReservations);
document.querySelector('input[name="from"][type="date"]').addEventListener('change', fetchReservations);
document.querySelector('input[name="to"][type="date"]').addEventListener('change', fetchReservations);
        function setActive(element) {
            const links = document.querySelectorAll('#navbar li a');
            links.forEach(link => link.classList.remove('active'));
            element.classList.add('active');
        }
        function toggleDropdown() {
        const dropdown = document.querySelector('.dropdown');
        dropdown.classList.toggle('open');
        }
        document.addEventListener('click', function (event) {
        const dropdown = document.querySelector('.dropdown');
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('open');
        }
        });
    
        // Function to filter the table based on the user's inputs
    function filterTable() {
        const idInput = document.querySelector('.car-model-filter input').value.toLowerCase();
        const statusSelect = document.querySelector('#carModelSelect').value;
        const fromDate = document.querySelector('input[name="from"][type="date"]').value;
        const toDate = document.querySelector('input[name="to"][type="date"]').value;

        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            const reservationId = row.cells[0].textContent.toLowerCase();
            const date = row.cells[1].textContent;
            const status = row.cells[3].textContent;

            let show = true;

            // Filter by Reservation ID
            if (idInput && !reservationId.includes(idInput)) {
                show = false;
            }

            // Filter by Reservation Status
            if (statusSelect && status !== statusSelect) {
                show = false;
            }

            // Filter by Date Range
            if (fromDate || toDate) {
                const reservationDate = new Date(date.split('/').reverse().join('-'));
                if (fromDate && reservationDate < new Date(fromDate)) {
                    show = false;
                }
                if (toDate && reservationDate > new Date(toDate)) {
                    show = false;
                }
            }

            // Show or hide the row based on filters
            row.style.display = show ? '' : 'none';
        });
    }

    // Attach event listeners to the filters
    document.querySelector('.car-model-filter input').addEventListener('input', filterTable);
    document.querySelector('#carModelSelect').addEventListener('change', filterTable);
    document.querySelector('input[name="from"][type="date"]').addEventListener('change', filterTable);
    document.querySelector('input[name="to"][type="date"]').addEventListener('change', filterTable);
    </script>
    </body>
</html>
