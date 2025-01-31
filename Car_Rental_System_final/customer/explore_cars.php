    <?php
    session_start();
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "car_rental_system";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="../images/logo.png">
        <title>Explore Cars</title>
        <link rel="stylesheet" href="../main/style.css">
        <link rel="stylesheet" href="../main/form.css">
        <link rel="stylesheet" href="explore_cars.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <style>
            /* Card Container Styles */
            body {
                height: 100%;
                /* Ensure body takes full height of the viewport */
                margin: 0;
                /* Remove default margin */
                padding: 0;
                /* Remove default padding */
                overflow-y: auto;
                /* Enable vertical scrolling for the whole page */
            }

            .card-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                justify-content: center;
                padding: 20px;
            }

            .car-card {
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                width: 300px;
                text-align: center;
                overflow: hidden;
                font-family: 'Quicksand', sans-serif;
            }

            .car-card img {
                width: 100%;
                height: 180px;
                object-fit: cover;
            }

            .car-card .card-content {
                padding: 15px;
            }

            .car-card .car-name {
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .car-card .car-year {
                color: #555;
                margin-bottom: 10px;
            }

            .car-card .car-details {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                font-size: 14px;
                color: #777;
            }

            .car-card .rent-button {
                background-color: #007BFF;
                color: white;
                border: none;
                border-radius: 5px;
                padding: 10px;
                font-size: 16px;
                cursor: pointer;
                transition: 0.3s;
                width: 100%;
            }

            .car-card .rent-button:hover {
                background-color: #0056b3;
            }

            .car-card .price {
                font-size: 18px;
                font-weight: bold;
                margin-top: 10px;
                color: #000;
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
                        <li><a href="explore_cars.php" class="active" onclick="setActive(this)">Explore Cars</a></li>
                        <li><a href="my_reservation.php" onclick="setActive(this)">My Reservations</a></li>
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
        <div class="manage-cars">
            <div class="filter-container">

                <!-- Car Model Filter -->
                <div class="car-model-filter">
                    <div class="filter-heading">Car Model</div>
                    <select id="carModelSelect" class="car-model-select">
                        <option value="">Select Car Model</option>
                        <?php
                        $model_query = "SELECT DISTINCT Model FROM cars WHERE Status = 'Active'";
                        $model_result = $conn->query($model_query);
                        while ($model_row = $model_result->fetch_assoc()) {
                            echo '<option value="' . $model_row['Model'] . '">' . $model_row['Model'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="year-filter">
                    <div class="filter-heading">Year</div>
                    <div class="year-container">
                        <input class="year-select" id="yearFrom" name="from" placeholder="From" type="number" min="1990" max="2024">
                        <input class="year-select" id="yearTo" name="to" placeholder="To" type="number" min="1990" max="2024">

                    </div>
                </div>
                <div class="price-filter">
                    <div class="filter-heading">Total Price</div>
                    <div class="price-container">
                        <input class="price-select" id="priceFrom" name="price-from" placeholder="From $" type="number" min="0">
                        <input class="price-select" id="priceTo" name="price-to" placeholder="To $" type="number" min="0">

                    </div>
                </div>


            </div>
            <div id="car-list" class="card-container">
                <?php
                $sql = "SELECT * FROM cars WHERE Status = 'Active'";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="car-card">
        <img src="' . $row['ImagePath'] . '" alt="' . $row['Model'] . '">
        <div class="card-content">
            <div class="car-name">' . $row['Model'] . '</div>
            <div class="car-year">Year: ' . $row['Year'] . '</div>
            <div class="car-details">
                <span>Plate ID: ' . $row['PlateID'] . '</span>
                <span>Office ID: ' . $row['OfficeID'] . '</span>
            </div>
            <div class="price">$' . $row['Price'] . '/month</div>
            <button class="rent-button" onclick="rentNow(' . $row['CarID'] . ')">Rent Now</button>
        </div>
    </div>';
                }
                ?>


            </div>
        </div>
        <div id="carFilterModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModalHideCars()">&times;</span>
                <h2>🚗 Save up to <strong>80%</strong> on Rental Cars!</h2>

                <div class="form-group">
                    <label for="pickup">📍 Pick-up Location</label>
                    <br>
                    <input type="text" id="pickup" placeholder="Enter pick-up location"><br><br>
                    <label for="dropoff">📍 Drop-off Location</label>
                    <br><br>
                    <input type="text" id="dropoff" placeholder="Enter drop-off location">
                </div>

                <div class="form-group">
                    <label for="pickupDate">📅 Pick-up Date</label>
                    <br>
                    <input type="date" id="pickupDate">
                </div>

                <div class="form-group">
                    <label for="dropoffDate">📅 Drop-off Date</label>
                    <br>
                    <input type="date" id="dropoffDate">
                </div>

                <button onclick="searchCars()">🔍 Search Cars</button>
            </div>
        </div>


        <script>
            function rentNow(carId) {
                // Get values from the filter modal
                const pickupLocation = document.getElementById('pickup').value;
                const returnLocation = document.getElementById('dropoff').value;
                const pickupDate = document.getElementById('pickupDate').value;
                const returnDate = document.getElementById('dropoffDate').value;

                if (!pickupLocation || !returnLocation || !pickupDate || !returnDate) {
                    alert('Please fill in all rental details first!');
                    document.getElementById('carFilterModal').style.display = 'block';
                    return;
                }

                // Redirect to checkout page with all necessary parameters
                window.location.href = `checkout.php?car_id=${carId}&pickup_date=${pickupDate}&return_date=${returnDate}&pickup_location=${encodeURIComponent(pickupLocation)}&return_location=${encodeURIComponent(returnLocation)}`;
            }

            function setActive(element) {
                const links = document.querySelectorAll('#navbar li a');
                links.forEach(link => link.classList.remove('active'));
                element.classList.add('active');
            }

            function toggleDropdown() {
                const dropdown = document.querySelector('.dropdown');
                dropdown.classList.toggle('open');
            }
            document.addEventListener('click', function(event) {
                const dropdown = document.querySelector('.dropdown');
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('open');
                }
            });
            document.getElementById('carModelSelect').addEventListener('change', filterCars);
            document.querySelectorAll('.year-select').forEach(input => {
                input.addEventListener('input', filterCars);
            });
            document.querySelectorAll('.price-select').forEach(input => {
                input.addEventListener('input', filterCars);
            });

            function filterCars() {
                const model = $('#carModelSelect').val();
                const yearFrom = $('#yearFrom').val();
                const yearTo = $('#yearTo').val();
                const priceFrom = $('#priceFrom').val();
                const priceTo = $('#priceTo').val();

                $.ajax({
                    url: 'fetch_cars.php',
                    type: 'GET',
                    data: {
                        model: model,
                        yearFrom: yearFrom,
                        yearTo: yearTo,
                        priceFrom: priceFrom,
                        priceTo: priceTo
                    },
                    success: function(data) {
                        $('#car-list').html(data);
                    }
                });
            }




            window.onload = function() {
                const modal = document.getElementById('carFilterModal');
                modal.style.display = 'block';
            }

            function closeModalHideCars() {
                const modal = document.getElementById('carFilterModal');
                modal.style.display = 'none';

                // Hide all car cards
                const carCards = document.querySelectorAll('#car-list .car-card');
                carCards.forEach(card => {
                    card.style.display = 'none';
                });
            }

            function closeModalKeepCars() {
                const modal = document.getElementById('carFilterModal');
                modal.style.display = 'none';

                // Rows remain visible (no additional logic needed here)
            }


            function searchCars() {
                const pickup = document.getElementById('pickup').value.trim();
                const pickupDate = document.getElementById('pickupDate').value.trim();
                const dropoffDate = document.getElementById('dropoffDate').value.trim();
                let numberOfDays = 1;

                if (pickup && pickupDate && dropoffDate) {
                    const pickupDateObj = new Date(pickupDate);
                    const dropoffDateObj = new Date(dropoffDate);

                    if (dropoffDateObj <= pickupDateObj) {
                        alert('🚨 Drop-off date cannot be before pick-up date or on the same day!');
                        return;
                    }

                    const timeDiff = Math.abs(dropoffDateObj - pickupDateObj);
                    numberOfDays = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                    // Filter cars dynamically
                    filterCars();

                    closeModalKeepCars();
                } else {
                    alert('🚨 Please fill all fields!');
                }
            }
        </script>
    </body>

    </html>