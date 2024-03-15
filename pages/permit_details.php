<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Data from MySQL Database</title>
    <!-- Include CSS and JS files for date and time pickers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        nav{
            display: flex;
        }

        .headerItems{
            padding: 0.5%;
        }
        .headerItems{
            padding:2%;
        }

        nav{
            margin-bottom: 2%;
        }
    </style>
</head>
<body>
<header>
    <div class="branding">
        <div>
            <h1>MGA ACCLING</h1>
        </div>
    </div>
    <nav>
        <a class="headerItems" href="server1.php">Create a Record</a>
        <a class="headerItems" href="usage_facility.php">All Tables</a>
        <a class="headerItems" href="formview.php">Usage Permit Form</a>
        <a class="headerItems" href="calendar_view.php">Calendar View</a>
    </nav>
</header>

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
            while($row = $result->fetch_assoc()) {
                echo "Usage Facility ID: " . $row["Usage_Faci_ID"]. "<br>";
                echo "Event Date: " . $row["Event_Date"]. "<br>";
                echo "Event Time: " . $row["Event_Time"]. "<br>";
                echo "Facility ID: " . $row["Facility_ID"]. "<br>";
                echo "Activity ID: " . $row["Activity_ID"]. "<br>";
                echo "Permit ID: " . $row["Permit_ID"]. "<br>";
            }
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