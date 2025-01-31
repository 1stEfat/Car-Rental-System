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

// Fetch customer data
$customerID = $_SESSION['CustomerID']; // Assuming you store the CustomerID in the session
$query = "SELECT * FROM customers WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Update customer data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['FirstName'];
    $lastName = $_POST['LastName'];
    $phoneNumber = $_POST['PhoneNumber'];
    $address = $_POST['Address'];

    $updateQuery = "UPDATE customers SET FirstName = ?, LastName = ?, PhoneNumber = ?, Address = ? WHERE CustomerID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssi", $firstName, $lastName, $phoneNumber, $address, $customerID);
    $updateStmt->execute();

    // Refresh customer data
    header("Location: profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo.png">
    <title>Profile</title>
    <link rel="stylesheet" href="../main/style.css">
    <link rel="stylesheet" href="../main/form.css">
    <link rel="stylesheet" href="explore_cars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
html, body {
    height: 100%; /* Ensure body takes full height of the viewport */
    margin: 0; /* Remove default margin */
    padding: 0; /* Remove default padding */
    overflow-y: auto; /* Enable vertical scrolling for the whole page */
}

.profile-container {
    width: 50%;
    margin: 50px auto; /* Center the profile container */
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding-bottom: 20px; /* Adding some space at the bottom */
}

.profile-header {
    background-color: #3498db;
    color: #ffffff;
    padding: 20px;
    text-align: center;
}

.profile-body {
    padding: 20px;
}

.profile-body form {
    display: flex;
    flex-direction: column;
}

.profile-body form .form-group {
    margin-bottom: 15px;
}

.profile-body form .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.profile-body form .form-group input, 
.profile-body form .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.profile-body form .form-group textarea {
    resize: none;
    height: 100px;
}

.profile-body form button {
    background-color: #3498db;
    color: #ffffff;
    border: none;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.profile-body form button:hover {
    background-color: #2980b9;
}

/* Allow the rest of the page to scroll if needed */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* Ensures the page takes the full viewport height */
}

footer {
    margin-top: auto; /* Pushes the footer to the bottom if content is not enough */
}

    </style>
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
    <div class="profile-container">
        <div class="profile-header">
            <h2>My Profile</h2>
        </div>
        <div class="profile-body">
            <form method="POST" action="profile.php">
                <div class="form-group">
                    <label for="FirstName">First Name</label>
                    <input type="text" name="FirstName" id="FirstName" value="<?php echo htmlspecialchars($customer['FirstName']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="LastName">Last Name</label>
                    <input type="text" name="LastName" id="LastName" value="<?php echo htmlspecialchars($customer['LastName']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="Email">Email</label>
                    <input type="email" id="Email" value="<?php echo htmlspecialchars($customer['Email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="PhoneNumber">Phone Number</label>
                    <input type="text" name="PhoneNumber" id="PhoneNumber" value="<?php echo htmlspecialchars($customer['PhoneNumber']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="Address">Address</label>
                    <textarea name="Address" id="Address" required><?php echo htmlspecialchars($customer['Address']); ?></textarea>
                </div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
    <script>
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
</script>
</body>

</html>