<?php
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="manage_cars.css">
    <link rel="stylesheet" href="payments.css">
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
            <li><a href="manage_cars.php" class="active"><i class="fa-solid fa-car"></i> Manage Cars</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a></li>
            <li><a href="reservations.php"><i class="fa-solid fa-calendar-check"></i> Reservations</a></li>
            <li><a href="payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a></li>
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


        <!-- Manage Cars Section -->
         
        <div class="manage-cars">
            <h1>Manage Cars</h1>

            <div class="filter-label">Filter By:</div>

            <div class="filter-container">
            
                <!-- Car Model Filter -->
                <div class="car-model-filter">
                    <div class="filter-heading">Car Model</div>
                    <select id="carModelSelect" class="car-model-select">
                        
                        <option value="">Select Car Model</option>
                        <option value="Toyota Corolla">Toyota Corolla</option>
                        <option value="Honda Civic">Honda Civic</option>
                        <option value="Ford Focus">Ford Focus</option>Nissan Altima
                        <option value="Nissan Altima">Nissan Altima</option>
                        <option value="BMW 3 Series">BMW 3 Series</option>
                    </select>
                </div>        

                <div class="year-filter">
                    <div class="filter-heading">Year</div>
                    <div class="year-container">
                        <input class="year-select" name="from" placeholder="From" type="number" min="1990" max="2024">
                        <input class="year-select" name="to" placeholder="To" type="number" min="1990" max="2024">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="status-filter">
                    <div class="filter-heading">Status</div>
                    <div class="status-buttons">
                        <button class="filter-btn" onclick="filterByStatus('Active')">Active</button>
                        <button class="filter-btn" onclick="filterByStatus('Out of Service')">Out of Service</button>
                        <button class="filter-btn" onclick="filterByStatus('Rented')">Rented</button>
                    </div>
                </div>
                
            </div>        



            <button class="btn-add-car" onclick="openPopup()">+ Add New Car</button>
            <table>
                <thead>
                    <tr>
                        <th>Car Model</th>
                        <th>Year</th>
                        <th>Plate ID</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                   
                 
                        <td><span class="badge badge-out">Out of Service</span></td>
                        <td>
                            <button class="btn-edit"><i class="fa-solid fa-pen-to-square"></i>Update Status</button>
                            <button class="btn-delete"><i class="fa-solid fa-trash"></i>Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        

            <div id="popupForm">
    <form id="addCarForm" method="post" action="manage_cars.php" enctype="multipart/form-data">
        <button class="close-btn" onclick="closePopup()" type="button">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h2>Add New Car</h2>

        <label for="carName">Car Model</label>
        <input type="text" id="carName" name="carName" required>

        <label for="carYear">Year</label>
        <input type="number" id="carYear" name="carYear" min="1990" max="2024" required>

        <label for="carPlate">Plate ID</label>
        <input type="text" id="carPlate" name="carPlate" required>

        <label for="price">price</label>
        <input type="number" id="price" name="price" required>


        <label for="status">Status</label>
        <select name="status" id="status" class="car-model-select" required>
            <option value="Active">Active</option>
            <option value="Rented">Rented</option>
            <option value="Out of Service">Out of Service</option>
        </select>

        <label for="carImage">Car Image</label>
        <input type="file" id="carImage" name="carImage" accept="image/*">

        <button name="add-car" type="submit" class="add-car">Add Car</button>
    </form>
</div>
<?php
if (isset($_POST['add-car'])) {
   

    $Model = htmlspecialchars($_POST['carName']);
    $Year = (int)$_POST['carYear'];
    $PlateID = htmlspecialchars($_POST['carPlate']);
    $Status = htmlspecialchars($_POST['status']);
    $Price = htmlspecialchars($_POST['price']);

    
    $check_sql = "SELECT PlateID FROM cars WHERE PlateID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $PlateID);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        
        echo "<script>alert('Error: PlateID already exists. Please use a unique PlateID.');</script>";
    } else {
        
        $sql = "INSERT INTO cars (Model, Year, PlateID, Status, price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $OfficeID = 1; // Default OfficeID
        $stmt->bind_param("sissi", $Model, $Year, $PlateID, $Status, $Price);

        if ($stmt->execute()) {
            $Model = $Year = $PlateID = $Status = "";
            echo "<script>alert('Car added successfully!');</script>";
            
            
        } else {
            die("Error: " . $stmt->error);
        }
    }

    $check_stmt->close();
    $conn->close();
}
?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('addCarForm');

        form.addEventListener('submit', (event) => {
            setTimeout(() => {
                form.reset(); // Reset all form fields
            }, 500);
        });
    });
</script>
    

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        }

        // Popup functionality
        function openPopup() {
        document.getElementById('popupForm').style.display = 'flex';
        }

        function closePopup() {
        document.getElementById('popupForm').style.display = 'none';
        }

        window.onclick = function(event) {
        const popup = document.getElementById('popupForm');
        if (event.target == popup) {
            closePopup();
        }
     }
     document.addEventListener("DOMContentLoaded", () => {
  // Elements
  const carModelSelect = document.getElementById("carModelSelect");
  const yearFromInput = document.querySelector("input[name='from']");
  const yearToInput = document.querySelector("input[name='to']");
  const filterButtons = document.querySelectorAll(".status-buttons .filter-btn");
  const tableBody = document.querySelector("table tbody");

  // Initialize status filter
  let selectedStatus = "";

  // Add event listeners to status filter buttons
  filterButtons.forEach(button => {
    button.addEventListener("click", () => {
      filterByStatus(button.innerText); // Call filterByStatus
      filterCars(); // Trigger filtering
    });
  });

  // Function to handle button activity and filtering logic
  function filterByStatus(status) {
    // Remove the 'active' class from all buttons
    filterButtons.forEach(button => button.classList.remove('active'));

    // Add the 'active' class to the clicked button
    const clickedButton = Array.from(filterButtons).find(button => button.textContent === status);
    if (clickedButton) {
      clickedButton.classList.add('active');
    }

    // Set the selected status
    selectedStatus = status;
    console.log("Filtering by:", status); // Debugging log
  }

  // Fetch and filter cars
  function filterCars() {
    const filters = {
      model: carModelSelect.value,
      yearFrom: yearFromInput.value,
      yearTo: yearToInput.value,
      status: selectedStatus
    };

    // Send filters to PHP backend via AJAX
    fetch("filter_cars.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(filters)
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateTable(data.cars); // Update the table with filtered cars
        } else {
          alert("Error fetching filtered cars!");
        }
      })
      .catch(error => {
        console.error("Error:", error);
      });
  }

  // Update table dynamically
  function updateTable(cars) {
  tableBody.innerHTML = ""; // Clear existing rows

  cars.forEach(car => {
    const row = document.createElement("tr");
    const badgeClass = car.Status === "Active"
      ? "badge-active"
      : car.Status === "Out of Service"
      ? "badge-out"
      : "badge-rented";

    row.innerHTML = `
      <td>${car.Model}</td>
      <td>${car.Year}</td>
      <td>${car.PlateID}</td>
      <td><span class="badge ${badgeClass}">${car.Status}</span></td>
      <td>
        <button class="btn-edit" data-id="${car.PlateID}" data-status="${car.Status}">
          <i class="fa-solid fa-pen-to-square"></i> Update Status
        </button>
        <button class="btn-delete" data-id="${car.PlateID}">
          <i class="fa-solid fa-trash"></i> Delete
        </button>
      </td>
    `;

    tableBody.appendChild(row);
  });


  document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', event => {
      const plateID = event.target.closest('button').dataset.id;
      const currentStatus = event.target.closest('button').dataset.status;
      updateCarStatus(plateID, currentStatus);
    });
  });

  document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', event => {
      const plateID = event.target.closest('button').dataset.id;
      deleteCar(plateID);
    });
  });

}

// Fetch all cars on page load
filterCars();

});

function updateCarStatus(plateID, currentStatus) {
  const newStatus = prompt(`Current status: ${currentStatus}. Enter new status (Active, Rented, Out of Service):`);

  if (newStatus && ['Active', 'Rented', 'Out of Service'].includes(newStatus)) {
    fetch('manage_cars.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'update',
        plateID: plateID,
        newStatus: newStatus,
      }),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Status updated successfully!');
          filterCars(); // Refresh the table
        } else {
          alert('Failed to update status.');
        }
      })
      .catch(error => console.error('Error:', error));
  } else {
    alert('Invalid status entered.');
  }
}

function deleteCar(plateID) {
  if (confirm('Are you sure you want to delete this car?')) {
    fetch('manage_cars.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'delete',
        plateID: plateID,
      }),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Car deleted successfully!');
          filterCars(); // Refresh the table
        } else {
          alert('Failed to delete car.');
        }
      })
      .catch(error => console.error('Error:', error));
  }
}




    </script>

<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        $action = $input['action'];

        if ($action === 'update' && isset($input['plateID'], $input['newStatus'])) {
            $plateID = $input['plateID'];
            $newStatus = $input['newStatus'];

            $sql = "UPDATE cars SET Status = ? WHERE PlateID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $newStatus, $plateID);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }

            $stmt->close();
        }

        if ($action === 'delete' && isset($input['plateID'])) {
            $plateID = $input['plateID'];

            $sql = "DELETE FROM cars WHERE PlateID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $plateID);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }

            $stmt->close();
        }
    }
}


?>

</body>
</html>
