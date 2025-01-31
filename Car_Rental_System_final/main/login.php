<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Car_Rental_System";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM Customers WHERE Email='$email'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $hashed_password = $row['PasswordHash'];
    if (password_verify($password, $hashed_password)) { 
        $_SESSION['Name']= $row['FirstName'] . ' ' . $row['LastName'];
        $_SESSION['CustomerID'] = $row['CustomerID'];
        echo "<script>alert('Logged In Successfully!'); window.location.href='../customer/explore_cars.php';</script>";
    } else {
        echo "<script>alert('Invalid email or password!'); window.location.href='login.html';</script>";
    }
} else {
    echo "<script>alert('Invalid email or password!'); window.location.href='login.html';</script>";
}

mysqli_close($conn);
?>