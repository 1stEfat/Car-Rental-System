<?php
session_start();

// Database connection
function getDBConnection() {
    $conn = new PDO("mysql:host=localhost;dbname=car_rental_system", "root", "");
    return $conn;
}

// Get payments with filters
function getPayments($filters = []) {
    $conn = getDBConnection();
    $where_conditions = [];
    $params = [];

    // Add date from filter
    if (!empty($filters['dateFrom'])) {
        $where_conditions[] = "p.PaymentDate >= :dateFrom";
        $params[':dateFrom'] = $filters['dateFrom'];
    }

    // Add date to filter
    if (!empty($filters['dateTo'])) {
        $where_conditions[] = "p.PaymentDate <= :dateTo";
        $params[':dateTo'] = $filters['dateTo'];
    }

    // Add payment method filter
    if (!empty($filters['paymentMethod']) && $filters['paymentMethod'] !== 'all') {
        $where_conditions[] = "p.PaymentMethod = :paymentMethod";
        $params[':paymentMethod'] = $filters['paymentMethod'];
    }

    // Add status filter
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $where_conditions[] = "p.Status = :status";
        $params[':status'] = ucfirst(strtolower($filters['status']));
    }

    // Add amount range filters
    if (!empty($filters['amountFrom'])) {
        $where_conditions[] = "p.Amount >= :amountFrom";
        $params[':amountFrom'] = $filters['amountFrom'];
    }
    if (!empty($filters['amountTo'])) {
        $where_conditions[] = "p.Amount <= :amountTo";
        $params[':amountTo'] = $filters['amountTo'];
    }

    // Add customer search filter
    if (!empty($filters['customerSearch'])) {
        $where_conditions[] = "(cust.FirstName LIKE :customerName OR cust.LastName LIKE :customerName)";
        $params[':customerName'] = '%' . $filters['customerSearch'] . '%';
    }

    // Add car model search filter
    if (!empty($filters['carModelSearch'])) {
        $where_conditions[] = "cars.Model LIKE :carModel";
        $params[':carModel'] = '%' . $filters['carModelSearch'] . '%';
    }

    // Create WHERE clause
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Main query
    $sql = "SELECT 
        p.PaymentID as id,
        CONCAT(cust.FirstName, ' ', cust.LastName) as customer_name,
        cars.Model as car_model,
        p.Amount as amount,
        p.Status as status,
        DATE_FORMAT(p.PaymentDate, '%Y-%m-%d') as payment_date,
        DATEDIFF(r.ReturnDate, r.PickupDate) as period,
        p.PaymentMethod as payment_method,
        p.PickupLocation as pickup_location,
        p.DropoffLocation as dropoff_location
    FROM 
        payments p
    JOIN 
        reservations r ON p.ReservationID = r.ReservationID
    JOIN 
        customers cust ON r.CustomerID = cust.CustomerID
    JOIN 
        cars ON r.CarID = cars.CarID
    $where_clause";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get summary data with filters
function getSummaryData($filters = []) {
    $conn = getDBConnection();
    $where_conditions = [];
    $params = [];

    // Build filter conditions (same as getPayments function)
    if (!empty($filters['dateFrom'])) {
        $where_conditions[] = "p.PaymentDate >= :dateFrom";
        $params[':dateFrom'] = $filters['dateFrom'];
    }
    if (!empty($filters['dateTo'])) {
        $where_conditions[] = "p.PaymentDate <= :dateTo";
        $params[':dateTo'] = $filters['dateTo'];
    }
    if (!empty($filters['paymentMethod']) && $filters['paymentMethod'] !== 'all') {
        $where_conditions[] = "p.PaymentMethod = :paymentMethod";
        $params[':paymentMethod'] = $filters['paymentMethod'];
    }
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $where_conditions[] = "p.Status = :status";
        $params[':status'] = ucfirst(strtolower($filters['status']));
    }
    if (!empty($filters['amountFrom'])) {
        $where_conditions[] = "p.Amount >= :amountFrom";
        $params[':amountFrom'] = $filters['amountFrom'];
    }
    if (!empty($filters['amountTo'])) {
        $where_conditions[] = "p.Amount <= :amountTo";
        $params[':amountTo'] = $filters['amountTo'];
    }
    if (!empty($filters['customerSearch'])) {
        $where_conditions[] = "(cust.FirstName LIKE :customerName OR cust.LastName LIKE :customerName)";
        $params[':customerName'] = '%' . $filters['customerSearch'] . '%';
    }
    if (!empty($filters['carModelSearch'])) {
        $where_conditions[] = "cars.Model LIKE :carModel";
        $params[':carModel'] = '%' . $filters['carModelSearch'] . '%';
    }

    // Create base WHERE clause
    $base_where = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Add status conditions
    $completed_where = $base_where . (empty($where_conditions) ? "WHERE " : " AND ") . "p.Status = 'Completed'";
    $pending_where = $base_where . (empty($where_conditions) ? "WHERE " : " AND ") . "p.Status = 'Pending'";

    // Get total revenue (Completed payments)
    $sql_revenue = "SELECT COALESCE(SUM(p.Amount), 0) as total 
        FROM payments p
        LEFT JOIN reservations r ON p.ReservationID = r.ReservationID
        LEFT JOIN customers cust ON r.CustomerID = cust.CustomerID
        LEFT JOIN cars ON r.CarID = cars.CarID
        $completed_where";
    
    $stmt = $conn->prepare($sql_revenue);
    $stmt->execute($params);
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get pending amount
    $sql_pending = "SELECT COALESCE(SUM(p.Amount), 0) as total 
        FROM payments p
        LEFT JOIN reservations r ON p.ReservationID = r.ReservationID
        LEFT JOIN customers cust ON r.CustomerID = cust.CustomerID
        LEFT JOIN cars ON r.CarID = cars.CarID
        $pending_where";
        
    $stmt = $conn->prepare($sql_pending);
    $stmt->execute($params);
    $pendingAmount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total transactions count
    $sql_count = "SELECT COUNT(*) as count 
        FROM payments p
        LEFT JOIN reservations r ON p.ReservationID = r.ReservationID
        LEFT JOIN customers cust ON r.CustomerID = cust.CustomerID
        LEFT JOIN cars ON r.CarID = cars.CarID
        $base_where";
        
    $stmt = $conn->prepare($sql_count);
    $stmt->execute($params);
    $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    return [
        'totalRevenue' => number_format($totalRevenue, 2),
        'pendingAmount' => number_format($pendingAmount, 2),
        'totalCount' => $totalCount
    ];
}

// Export payments to CSV
function exportPaymentsToCSV($filters = []) {
    $payments = getPayments($filters);
    
    if (empty($payments)) {
        return false;
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payments_export_' . date('Y-m-d_His') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'ID',
        'Customer Name',
        'Car Model',
        'Amount',
        'Status',
        'Payment Date',
        'Period',
        'Payment Method',
        'Pickup Location',
        'Dropoff Location'
    ]);
    
    // Write data rows
    foreach ($payments as $payment) {
        fputcsv($output, [
            $payment['id'],
            $payment['customer_name'],
            $payment['car_model'],
            '$' . number_format($payment['amount'], 2),
            $payment['status'],
            $payment['payment_date'],
            $payment['period'],
            $payment['payment_method'],
            $payment['pickup_location'],
            $payment['dropoff_location']
        ]);
    }
    
    fclose($output);
    return true;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $filters = [
        'dateFrom' => $_GET['dateFrom'] ?? null,
        'dateTo' => $_GET['dateTo'] ?? null,
        'paymentMethod' => $_GET['paymentMethod'] ?? null,
        'status' => $_GET['status'] ?? null,
        'amountFrom' => $_GET['amountFrom'] ?? null,
        'amountTo' => $_GET['amountTo'] ?? null,
        'customerSearch' => $_GET['customerSearch'] ?? null,
        'carModelSearch' => $_GET['carModelSearch'] ?? null
    ];

    switch ($_GET['action']) {
        case 'getPayments':
            echo json_encode(getPayments($filters));
            break;
            
        case 'getSummary':
            echo json_encode(getSummaryData($filters));
            break;
            
        case 'exportCSV':
            exportPaymentsToCSV($filters);
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payments Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="payments.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php"><img src="../images/logo2.png" alt="Logo"></a>
        <ul>
            <li><a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
            <li><a href="manage_cars.php"><i class="fa-solid fa-car"></i> Manage Cars</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a></li>
            <li><a href="reservations.php"><i class="fa-solid fa-calendar-check"></i> Reservations</a></li>
            <li><a href="payments.php" class="active"><i class="fa-solid fa-credit-card"></i> Payments</a></li>
            <li><a href="../main/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Log out</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <button class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
            
            <div class="profile">
                <i class="fa-solid fa-circle-user fa-2x"></i>
                <span style="font-weight: 600;"><?php echo $_SESSION['Name']; ?></span> 
            </div>
        </header>
        
        <div class="summary-cards">
            <div class="card">
                <div class="card-title">Total Revenue</div>
                <div class="card-value" id="totalRevenue">$0.00</div>
            </div>
            <div class="card">
                <div class="card-title">Pending Payments</div>
                <div class="card-value" id="pendingAmount">$0.00</div>
            </div>
            <div class="card">
                <div class="card-title">Total Transactions</div>
                <div class="card-value" id="transactionCount">0</div>
            </div>
        </div>

        <div class="filter-section">
            <h2 style="margin-bottom: 16px;">Filters</h2>
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Date Range</label>
                    <input type="date" id="dateFrom" placeholder="From">
                    <input type="date" id="dateTo" placeholder="To">
                </div>
                <div class="filter-group">
                    <label>Payment Status</label>
                    <select id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Payment Method</label>
                    <select id="paymentMethodFilter">
                        <option value="all">All Methods</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Amount Range</label>
                    <input type="number" id="amountFrom" placeholder="Min Amount">
                    <input type="number" id="amountTo" placeholder="Max Amount">
                </div>
                <div class="filter-group">
                    <label>Customer Name</label>
                    <input type="text" id="customerSearch" placeholder="Search customer...">
                </div>
                <div class="filter-group">
                    <label>Car Model</label>
                    <input type="text" id="carModelSearch" placeholder="Search car model...">
                </div>
            </div>
            <div class="filter-buttons">
                <button class="button primary-button" onclick="applyFilters()">Apply Filters</button>
                <button class="button secondary-button" onclick="resetFilters()">Reset Filters</button>
                <button class="button secondary-button" onclick="exportToCSV()">Export Results</button>
            </div>
        </div>

        <div class="transactions-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Car Model</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Period</th>
                            <th>Payment Method</th>
                            <th>Pick up Location</th>
                            <th>Drop off Location</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <!-- Table content will be dynamically populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        async function fetchPayments(filters = {}) {
        try {
            const queryParams = new URLSearchParams({
                action: 'getPayments',
                ...filters
            });
            const response = await fetch(`?${queryParams.toString()}`);
            const payments = await response.json();
            renderPayments(payments);
            updateSummary();
        } catch (error) {
            console.error('Error fetching payments:', error);
        }
    }

        function updateSummary() {
    const filters = {
        dateFrom: document.getElementById('dateFrom').value,
        dateTo: document.getElementById('dateTo').value,
        status: document.getElementById('statusFilter').value,
        paymentMethod: document.getElementById('paymentMethodFilter').value,
        amountFrom: document.getElementById('amountFrom').value,
        amountTo: document.getElementById('amountTo').value,
        customerSearch: document.getElementById('customerSearch').value,
        carModelSearch: document.getElementById('carModelSearch').value
    };

    const queryParams = new URLSearchParams({
        action: 'getSummary',
        ...filters
    });

    fetch(`?${queryParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalRevenue').textContent = `$${data.totalRevenue}`;
            document.getElementById('pendingAmount').textContent = `$${data.pendingAmount}`;
            document.getElementById('transactionCount').textContent = data.totalCount;
        })
        .catch(error => {
            console.error('Error updating summary:', error);
        });
}

        // Call updateSummary when page loads and after any filter changes
        document.addEventListener('DOMContentLoaded', () => {
            updateSummary();
            fetchPayments();
        });

        // Update the applyFilters function to refresh summary
        function applyFilters() {
    const filters = {
        dateFrom: document.getElementById('dateFrom').value,
        dateTo: document.getElementById('dateTo').value,
        status: document.getElementById('statusFilter').value,
        paymentMethod: document.getElementById('paymentMethodFilter').value,
        amountFrom: document.getElementById('amountFrom').value,
        amountTo: document.getElementById('amountTo').value,
        customerSearch: document.getElementById('customerSearch').value,
        carModelSearch: document.getElementById('carModelSearch').value
    };
    fetchPayments(filters);
    updateSummary(); // This will now use the same filters
}

function resetFilters() {
    // Reset all filter inputs
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('paymentMethodFilter').value = 'all';
    document.getElementById('amountFrom').value = '';
    document.getElementById('amountTo').value = '';
    document.getElementById('customerSearch').value = '';
    document.getElementById('carModelSearch').value = '';
    
    // Fetch data without filters
    fetchPayments();
    updateSummary();
}

function renderPayments(payments) {
        const tableBody = document.getElementById('paymentsTableBody');
        tableBody.innerHTML = '';

        payments.forEach(payment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${payment.id}</td>
                <td>${payment.customer_name}</td>
                <td>${payment.car_model}</td>
                <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                <td>
                    <span class="status-badge status-${payment.status.toLowerCase()}">
                        ${payment.status}
                    </span>
                </td>
                <td>${payment.payment_date}</td>
                <td>${payment.period} days</td>
                <td>${payment.payment_method}</td>
                <td>${payment.pickup_location}</td>
                <td>${payment.dropoff_location}</td>
            `;
            tableBody.appendChild(row);
        });
    }


        // Initialize the page

        document.addEventListener('DOMContentLoaded', () => {
    fetchPayments();
    updateSummary();
});
function exportToCSV() {
        const filters = {
            dateFrom: document.getElementById('dateFrom').value,
            dateTo: document.getElementById('dateTo').value,
            status: document.getElementById('statusFilter').value,
            paymentMethod: document.getElementById('paymentMethodFilter').value,
            amountFrom: document.getElementById('amountFrom').value,
            amountTo: document.getElementById('amountTo').value,
            customerSearch: document.getElementById('customerSearch').value,
            carModelSearch: document.getElementById('carModelSearch').value
        };

        window.location.href = `?action=exportCSV&${new URLSearchParams(filters).toString()}`;
    }

    function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        }
    </script>
</body>

</html>