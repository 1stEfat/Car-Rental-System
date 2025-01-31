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

$firstName = $_POST['first-name'];
$lastName = $_POST['last-name'];
$email = $_POST['email'];
$phoneNumber = $_POST['phone'];
$address = $_POST['address'];
$password = $_POST['password'];
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$checkEmail = "SELECT * FROM Customers WHERE Email='$email'";
$result = mysqli_query($conn, $checkEmail);

if (mysqli_num_rows($result) > 0) {
    echo "<script>alert('Email Already Exists!'); window.location.href='signup.html';</script>";
} else {
    $sql = "INSERT INTO Customers (FirstName, LastName, Email, PasswordHash, PhoneNumber, Address) VALUES ('$firstName', '$lastName', '$email', '$hashed_password', '$phoneNumber', '$address')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['Name']= $row['FirstName'] . ' ' . $row['LastName'];
      echo "<script>alert('Signed Up Successfully!'); window.location.href='../customer/explore_cars.php';</script>";
    }
    else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>