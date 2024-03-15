<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Data from MySQL Database</title>
    <!-- Include CSS and JS files for date and time pickers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel = "stylesheet" href ="../css/styles.css">
    <link rel = "stylesheet" href ="../css/details.css">

</head>
<body>
<header>
    <div class="header-container">
        <a href="../home.php"><img src="../images/MMCM_Logo_noname.png" alt="Header Image"></a>
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

<div class="port-image">
    <h2>Permit Details</h2>
</div

<table class="mainContents" border="1">
<?php
// Connect to the database
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

// Check if date parameter is set
if(isset($_GET['date'])) {
    // Get the date from the URL parameter
    $date = $_GET['date'];

    // Query to fetch permit details for the selected date
    $permit_query = "SELECT * FROM usage_facility WHERE Event_Date = '$date'";
    
    // Execute the query
    $result = $conn->query($permit_query);

    // Check if query was successful
    if ($result) {
        if ($result->num_rows > 0) {
            // Output permit details
            echo '<div class="permit-details">';
            while($row = $result->fetch_assoc()) {
                echo "<b>Usage Facility ID:</b> " . $row["Usage_Faci_ID"]. "<br>";
                echo "<b>Event Date:</b> " . $row["Event_Date"]. "<br>";
                echo "<b>Event Time:</b> " . $row["Event_Time"]. "<br>";
                echo "<b>Facility ID:</b> " . $row["Facility_ID"]. "<br>";
                echo "<b>Activity ID:</b> " . $row["Activity_ID"]. "<br>";
                echo "<b>Permit ID:</b> " . $row["Permit_ID"]. "<br>";
            }
            echo "</div>";
        } else {
            echo "No permit details found for the selected date.";
        }
    } else {
        echo "Error executing query: " . $conn->error;
    }

    } else {
        echo "Date parameter is missing.";
    }


// Close the database connection
$conn->close();
?>

</body>
</html>