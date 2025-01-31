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

$admin_id = $_POST['admin-id'];
$password = $_POST['password'];

$query = "SELECT * FROM Admins WHERE AdminID='$admin_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($password == $row['Password']) {
        $_SESSION['Name'] = $row['FirstName'] . ' ' . $row['LastName']; 
        $_SESSION['CustomerID'] = $row['CustomerID'];
        
        echo "<script>alert('Logged In Successfully!'); window.location.href='../admin/dashboard.php';</script>";
    } else {
        echo "<script>alert('Invalid password!'); window.location.href='admin_login.html';</script>";
    }
} else {
    echo "<script>alert('Invalid email or password!'); window.location.href='login.html';</script>";
}

mysqli_close($conn);
?>