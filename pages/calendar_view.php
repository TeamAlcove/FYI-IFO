<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYI-IFO | Calendar View</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel = "stylesheet" href ="../css/styles.css">
    <link rel = "stylesheet" href ="../css/calendar.css">
    <style>
        /* Add CSS for calendar container */
        .calendar-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .calendar-container div {
            text-align: center;
            padding: 10px;
            border: 1px solid #ccc;
            cursor: pointer; /* Add cursor pointer to indicate clickable */
        }
        .occupied {
            background-color: #ffcccc;
            font-weight: bold;
        }
        .month-select {
            margin-bottom: 10px;
        }

        .headerItems{
            padding:2%;
        }

    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href = "../home.php"><img src="../images/MMCM_Logo_noname.png" alt="Header Image"></a>
        <h1>Institutional Facilities Office</h1> 
        <nav>
            <div class="navbar">
                <ul>
                    <li><a class="headerItems" href="server1.php">Create a Record</a></li>
                    <li><a class="headerItems" href="server2.php">Usage Permit</a></li>
                    <li><a class="headerItems" href="calendar_view.php">Calendar View</a></li>
                    <li><a class="headerItems" href="usage_facility.php">All Tables</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<div class = "port-image">
    <h2>Calendar View</h2>
</div>


<!-- Month and facility selection form -->
<form method="GET">
    <label for="facility">Select Facility:</label>
    <select name="facility" id="facility">
        <!-- Populate the options dynamically -->
        <?php
        // Fetch facility names from the database
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "im_final";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch facility names
        $facility_query = "SELECT Facility_Name FROM facilities";
        $facility_result = $conn->query($facility_query);
        if ($facility_result->num_rows > 0) {
            while ($row = $facility_result->fetch_assoc()) {
                $facility_name = $row['Facility_Name'];
                echo "<option value='$facility_name'>$facility_name</option>";
            }
        }

        // Close the database connection
        $conn->close();
        ?>
    </select>

    <label for="month">Select Month:</label>
    <input type="month" id="month" name="month">

    <input type="submit" value="Generate Calendar">
</form>

<!-- Calendar display -->
<div class="calendar-container">
<?php
if (isset($_GET['facility']) && isset($_GET['month'])) {
    $facility = $_GET['facility'];
    $month = $_GET['month'];

    // Convert month to date format (YYYY-MM-DD) to get the number of days in the month
    $days_in_month = date('t', strtotime($month));

    // Get the first day of the month
    $first_day = date('N', strtotime($month));

    // Fetch occupied dates for the selected facility from the database
    $occupied_dates = []; // Array to store occupied dates
    // Database connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch occupied dates from the usage_facility table
    $occupied_query = "SELECT Event_Date FROM usage_facility WHERE Facility_ID = (SELECT Facility_ID FROM facilities WHERE Facility_Name = '$facility') AND Event_Date LIKE '$month%'";

    $occupied_result = $conn->query($occupied_query);
    if ($occupied_result->num_rows > 0) {
        while ($row = $occupied_result->fetch_assoc()) {
            $occupied_dates[] = $row['Event_Date'];
        }
    }

    // Close the database connection
    $conn->close();

    // Generate calendar days
    for ($i = 1; $i < $first_day; $i++) {
        echo "<div></div>"; // Empty cells before the first day of the month
    }
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = date('Y-m-d', strtotime("$month-$day"));
        $class = in_array($date, $occupied_dates) ? 'occupied' : '';
        // Output link for bold dates with the date parameter
        if ($class == 'occupied') {
            echo "<a href='permit_details.php?date=$date' class='$class'>$day</a>";
        } else {
            echo "<div class='$class'>$day</div>";
        }
    }
} else {
    echo "<p>Please select a facility and a month to display the calendar.</p>";
}
?>
</div>


<!-- Include Flatpickr library for date selection -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize Flatpickr for date selection
    flatpickr('#month', {
        dateFormat: 'Y-m',
        disableMobile: true,
    });

    // Function to show details when a date is clicked
    function showDetails(date) {
        var permitID = <?php echo json_encode($occupied_dates); ?>[date];
        if (permitID) {
            // Redirect to a page to display details (you can customize this)
            window.location.href = "permit_details.php?permit_id=" + permitID;
        }
    }
</script>

</body>
</html>