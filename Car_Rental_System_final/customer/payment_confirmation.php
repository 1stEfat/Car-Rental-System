<?php
session_start();

if (!isset($_SESSION['payment_success']) || !isset($_SESSION['reservation_id'])) {
    header("Location: explore_cars.php");
    exit();
}

// Get the payment details from session
$paymentDetails = $_SESSION['payment_details'];
$reservationId = $_SESSION['reservation_id'];

// Clear the session variables
unset($_SESSION['payment_success']);
unset($_SESSION['reservation_id']);
unset($_SESSION['payment_details']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Confirmation</title>
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            color: #4CAF50;
            font-size: 48px;
            margin-bottom: 20px;
        }

        .confirmation-details {
            margin: 20px 0;
            text-align: left;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #45a049;
        }

        .redirect-message {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <div class="confirmation-details">
            <h2>Reservation Details</h2>
            <p>Reservation ID: <?php echo $reservationId; ?></p>
            <p>Car: <?php echo $paymentDetails['car_model'] . " " . $paymentDetails['car_year']; ?></p>
            <p>Pickup Date: <?php echo date('F d, Y', strtotime($paymentDetails['pickup_date'])); ?></p>
            <p>Return Date: <?php echo date('F d, Y', strtotime($paymentDetails['return_date'])); ?></p>
            <p>Payment Method: <?php echo $paymentDetails['payment_method']; ?></p>
            <p>Total Amount: $<?php echo number_format($paymentDetails['amount'], 2); ?></p>
        </div>
        <a href="explore_cars.php" class="button">Return to Car Listings</a>
        <p class="redirect-message">You will be redirected automatically in <span id="countdown">5</span> seconds...</p>
    </div>

    <script>
        // Countdown timer and redirect
        let timeLeft = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = 'my_reservation.php';
            }
        }, 1000);
    </script>
</body>
</html>