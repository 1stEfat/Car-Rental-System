<?php
session_start();

// Database connection
function getDBConnection() {
    $conn = new PDO("mysql:host=localhost;dbname=car_rental_system", "root", "");
    return $conn;
}

$carId = filter_input(INPUT_GET, 'car_id', FILTER_VALIDATE_INT);
$pickupDate = filter_input(INPUT_GET, 'pickup_date', FILTER_SANITIZE_STRING);
$returnDate = filter_input(INPUT_GET, 'return_date', FILTER_SANITIZE_STRING);
$pickupLocation = filter_input(INPUT_GET, 'pickup_location', FILTER_SANITIZE_STRING);
$returnLocation = filter_input(INPUT_GET, 'return_location', FILTER_SANITIZE_STRING);

if (!isset($_SESSION['CustomerID']) || !$carId || !$pickupDate || !$returnDate || !$pickupLocation || !$returnLocation) {
    header("Location: explore_cars.php");
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get car and office details
    $stmt = $conn->prepare("SELECT c.*, o.Address as OfficeAddress, o.OfficeName 
                           FROM cars c 
                           JOIN offices o ON c.OfficeID = o.OfficeID 
                           WHERE c.CarID = ? AND c.Status = 'Active'");
    $stmt->execute([$carId]);
    $carDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$carDetails) {
        $_SESSION['error'] = "Car not available";
        header("Location: explore_cars.php");
        exit();
    }

    // Calculate rental period and costs
    $pickup = new DateTime($pickupDate);
    $return = new DateTime($returnDate);
    $rentalDays = $return->diff($pickup)->days;
    
    $dailyRate = $carDetails['Price'];
    $rentalPrice = $dailyRate * $rentalDays;
    $taxRate = 0.14;
    $taxAmount = $rentalPrice * $taxRate;
    $insuranceRate = 20;
    $insuranceAmount = $insuranceRate * $rentalDays;
    $totalAmount = $rentalPrice + $taxAmount + $insuranceAmount;

    // Process payment submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $conn->beginTransaction();
            
            // Create reservation
            $stmt = $conn->prepare("INSERT INTO reservations (CustomerID, CarID, ReservationDate, PickupDate, ReturnDate, Status) 
                                  VALUES (?, ?, CURRENT_DATE, ?, ?, 'Reserved')");
            $stmt->execute([$_SESSION['CustomerID'], $carId, $pickupDate, $returnDate]);
            $reservationId = $conn->lastInsertId();
            
            // Create payment record
            $paymentMethod = $_POST['payment_method'];
            $paymentStatus = 'Completed';
            
            $stmt = $conn->prepare("INSERT INTO payments (ReservationID, PaymentDate, Amount, PaymentMethod, Status, PickupLocation, DropoffLocation) 
                                  VALUES (?, CURRENT_DATE, ?, ?, ?, ?, ?)");
            $stmt->execute([$reservationId, $totalAmount, $paymentMethod, $paymentStatus, $pickupLocation, $returnLocation]);
            
            // Update car status
            $stmt = $conn->prepare("UPDATE cars SET Status = 'Rented' WHERE CarID = ?");
            $stmt->execute([$carId]);
            
            $conn->commit();
            
            // Store reservation details in session
            $_SESSION['payment_success'] = true;
            $_SESSION['reservation_id'] = $reservationId;
            $_SESSION['payment_details'] = [
                'amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'car_model' => $carDetails['Model'],
                'car_year' => $carDetails['Year']
            ];
            
            header("Location: payment_confirmation.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Payment processing failed: " . $e->getMessage();
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Payment</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
<div class="container">
        <div class="card">
            <h2 style="margin-bottom: 24px;">Payment Details</h2>
            
            <div class="rental-details">
                <div class="rental-detail">
                    <label>Car Model</label>
                    <span><?php echo htmlspecialchars($carDetails['Model'] . " " . $carDetails['Year']); ?></span>
                </div>
                <div class="rental-detail">
                    <label>Rental Period</label>
                    <span><?php echo htmlspecialchars(date('M d', strtotime($pickupDate)) . " - " . date('M d', strtotime($returnDate)) . " ($rentalDays days)"); ?></span>
                </div>
                <div class="rental-detail">
                    <label>Pickup Location</label>
                    <span><?php echo htmlspecialchars($pickupLocation); ?></span>
                </div>
                <div class="rental-detail">
                    <label>Return Location</label>
                    <span><?php echo htmlspecialchars($returnLocation); ?></span>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="success-message" id="successMessage" style="display: none;">
                Payment processed successfully! Confirmation email has been sent.
            </div>

            <div class="payment-methods">
                <div class="payment-method" data-method="Credit Card">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Credit Card">
                    <span>Credit Card</span>
                </div>
                <div class="payment-method" data-method="Cash">
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxyZWN0IHg9IjIiIHk9IjQiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxNiIgcng9IjIiPjwvcmVjdD48bGluZSB4MT0iMiIgeTE9IjEyIiB4Mj0iMjIiIHkyPSIxMiI+PC9saW5lPjwvc3ZnPg==" alt="Cash">
                    <span>Cash</span>
                </div>
            </div>

            <!-- Credit Card Form -->
            <form id="paymentForm" method="POST">
                <input type="hidden" name="payment_method" value="Credit Card">
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" id="cardName" required>
                    <div class="error" id="nameError">Please enter cardholder name</div>
                </div>

                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" id="cardNumber" maxlength="16" required>
                    <div class="error" id="numberError">Please enter a valid card number</div>
                </div>

                <div class="card-grid">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" id="expiryDate" placeholder="MM/YY" maxlength="5" required>
                        <div class="error" id="expiryError">Please enter a valid expiry date</div>
                    </div>

                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" id="cvv" maxlength="3" required>
                        <div class="error" id="cvvError">Please enter a valid CVV</div>
                    </div>
                </div>

                <button type="submit" class="button" id="submitButton">Pay $<?php echo number_format($totalAmount, 2); ?></button>
            </form>

            <!-- Cash Payment Form -->
            <form id="cashForm" class="cash-form" style="display: none;" method="POST">
                <input type="hidden" name="payment_method" value="Cash">
                <div class="cash-notice">
                    Please note that cash payment must be made at the rental location before picking up the vehicle.
                </div>
                <button type="submit" class="button">Confirm Cash Payment</button>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 24px;">Order Summary</h2>
            
            <div class="summary-item">
                <span>Car Rental (<?php echo $rentalDays; ?> days)</span>
                <span>$<?php echo number_format($rentalPrice, 2); ?></span>
            </div>
            <div class="summary-item">
                <span>Insurance</span>
                <span>$<?php echo number_format($insuranceAmount, 2); ?></span>
            </div>
            <div class="summary-item">
                <span>Taxes (14%)</span>
                <span>$<?php echo number_format($taxAmount, 2); ?></span>
            </div>
            
            <div class="summary-item summary-total">
                <span>Total</span>
                <span>$<?php echo number_format($totalAmount, 2); ?></span>
            </div>
        </div>
    </div>

    <script>

const paymentMethods = document.querySelectorAll('.payment-method');
        const cardForm = document.getElementById('paymentForm');
        const cashForm = document.getElementById('cashForm');

        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');

                const paymentType = method.dataset.method;
                if (paymentType === 'Credit Card') {
                    cardForm.style.display = 'block';
                    cashForm.style.display = 'none';
                    cardForm.querySelector('input[name="payment_method"]').value = 'Credit Card';
                } else {
                    cardForm.style.display = 'none';
                    cashForm.style.display = 'block';
                    cashForm.querySelector('input[name="payment_method"]').value = 'Cash';
                }
            });
        });

        function processCashPayment() {
            const submitButton = event.target;
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            setTimeout(() => {
                document.getElementById('successMessage').style.display = 'block';
                cashForm.style.display = 'none';
                document.querySelector('.payment-methods').style.display = 'none';
            }, 2000);
        }

        // Form validation
        const form = document.getElementById('paymentForm');
        const cardNumber = document.getElementById('cardNumber');
        const expiryDate = document.getElementById('expiryDate');
        const cvv = document.getElementById('cvv');

        function validateCardNumber(number) {
            return /^\d{16}$/.test(number);
        }

        function validateExpiryDate(expiry) {
            if (!/^\d{2}\/\d{2}$/.test(expiry)) return false;
            
            const [month, year] = expiry.split('/');
            const currentYear = new Date().getFullYear() % 100;
            const currentMonth = new Date().getMonth() + 1;

            if (parseInt(month) < 1 || parseInt(month) > 12) return false;
            if (parseInt(year) < currentYear) return false;
            if (parseInt(year) === currentYear && parseInt(month) < currentMonth) return false;

            return true;
        }

        function validateCVV(cvv) {
            return /^\d{3}$/.test(cvv);
        }

        // Format expiry date input
        expiryDate.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });

        // Allow only numbers in card number and CVV
        [cardNumber, cvv].forEach(input => {
            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
        });

        form.addEventListener('submit', (e) => {
    e.preventDefault();
    let isValid = true;

    // Validate card number
    if (!validateCardNumber(cardNumber.value)) {
        document.getElementById('numberError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('numberError').style.display = 'none';
    }

    // Validate expiry date
    if (!validateExpiryDate(expiryDate.value)) {
        document.getElementById('expiryError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('expiryError').style.display = 'none';
    }

    // Validate CVV
    if (!validateCVV(cvv.value)) {
        document.getElementById('cvvError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('cvvError').style.display = 'none';
    }

    if (isValid) {
        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';

        // Actually submit the form
        form.submit();
    }
});
    </script>
</body>
</html>