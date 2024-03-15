<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYI-IFO | Usage Permit</title>
    <!-- Include CSS and JS files for date and time pickers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="icon" type="image/x-icon" href="../images/MMCM_Logo_noname.png">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel = "stylesheet" href ="../css/styles.css">
    <link rel = "stylesheet" href ="../css/forms.css">
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
    <h2>Usage Permit</h2>
</div>

<table class="mainContents" border="1">
<?php
// Database connection
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

// Process form submission for the second form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset(
    $_POST['Req_Name'], 
    $_POST['Req_Email'], 
    $_POST['Office_Name'], 
    $_POST['Event_Name'], 
    $_POST['Event_Purpose'], 
    $_POST['Activity_Name'], 
    $_POST['Facility_Name'], 
    $_POST['Event_Date'], 
    $_POST['Event_Time'], 
    $_POST['Expected_Population'], 
    $_POST['Permit_Date'], 
    $_POST['Permit_Time'], 
    $_POST['Admin_Name'], 
    $_POST['Staff_Name']
)) {
    // Retrieve Office_ID based on Office_Name
    $event_date = $_POST['Event_Date'];
    $facility_name = $_POST['Facility_Name'];

    $check_query = "SELECT Event_Date FROM usage_facility WHERE Event_Date = ? AND Facility_ID = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("si", $event_date, $_POST['Facility_Name']);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "Error: An event with the same date and facility name already exists.<br>";
    
        // Find other facilities that are available on the same date
        $available_facilities_query = "SELECT DISTINCT f.Facility_ID, f.Facility_Name 
                                        FROM facilities f
                                        LEFT JOIN usage_facility uf ON f.Facility_ID = uf.Facility_ID AND uf.Event_Date = ?
                                        WHERE uf.Facility_ID IS NULL";
        $stmt_available_facilities = $conn->prepare($available_facilities_query);
        $stmt_available_facilities->bind_param("s", $event_date);
        $stmt_available_facilities->execute();
        $result_available_facilities = $stmt_available_facilities->get_result();
    
        if ($result_available_facilities->num_rows > 0) {
            echo "Other facilities available on " . $event_date . ":<br>";
            while ($row = $result_available_facilities->fetch_assoc()) {
                echo "Facility ID: " . $row['Facility_ID'] . ", Facility Name: " . $row['Facility_Name'] . "<br>";
            }
        } else {
            echo "No other facilities are available on " . $event_date . ".<br>";
        }
    
        // Find the next available date for the selected facility
        $next_available_date_query = "SELECT MIN(Event_Date) AS Next_Available_Date
                                        FROM usage_facility
                                        WHERE Facility_ID = ? AND Event_Date > ?";
        $stmt_next_available_date = $conn->prepare($next_available_date_query);
        $stmt_next_available_date->bind_param("is", $facility_id, $event_date);
        $stmt_next_available_date->execute();
        $result_next_available_date = $stmt_next_available_date->get_result();
    
        if ($result_next_available_date->num_rows > 0) {
            $next_date_row = $result_next_available_date->fetch_assoc();
            $next_available_date = $next_date_row['Next_Available_Date'];
            echo "<br>Next available date for the selected facility: " . $next_available_date;
        } else {
            echo "<br>No next available date found for the selected facility.";
        }
        exit; 
    }

    // Retrieve Facility_Capacity based on Facility_ID
    $facility_id = $_POST['Facility_Name'];
    $expected_population = $_POST['Expected_Population'];

    // Query to retrieve Facility_Capacity
    $capacity_query = "SELECT Facility_Capacity FROM facilities WHERE Facility_ID = ?";
    $stmt_capacity = $conn->prepare($capacity_query);
    $stmt_capacity->bind_param("i", $facility_id);
    $stmt_capacity->execute();
    $result_capacity = $stmt_capacity->get_result();

    // Check if Facility_Capacity is retrieved successfully
    if ($result_capacity->num_rows > 0) {
        $capacity_row = $result_capacity->fetch_assoc();
        $facility_capacity = $capacity_row['Facility_Capacity'];

        // Compare Expected Population with Facility Capacity
        if ($expected_population > $facility_capacity) {
            // Check for facilities with capacity greater than expected population
            $capacity_query = "SELECT Facility_ID, Facility_Name, Facility_Capacity FROM facilities WHERE Facility_Capacity > ?";
            $stmt_capacity = $conn->prepare($capacity_query);
            $stmt_capacity->bind_param("i", $expected_population);
            $stmt_capacity->execute();
            $result_capacity = $stmt_capacity->get_result();

            // Check if facilities with greater capacity are found
            if ($result_capacity->num_rows > 0) {
                echo "Error: Expected Population of " . $expected_population . " exceeds Facility Capacity. Consider the following facilities with greater capacity:<br>";
                while ($row = $result_capacity->fetch_assoc()) {
                    echo "Facility ID: " . $row['Facility_ID'] . ", Facility Name: " . $row['Facility_Name'] . ", Facility Capacity: " . $row['Facility_Capacity'] . "<br>";
                }
                exit; // Stop further execution
            } else {
                echo "Error: Facility Capacity not found.";
                exit; // Stop further execution
            }
        }
    } else {
        echo "Error: Facility Capacity not found.";
        exit; // Stop further execution
    }

    $office_id = $_POST['Office_Name']; // Assuming Office_Name contains the ID
    $req_name = $_POST['Req_Name'];
    $req_email = $_POST['Req_Email'];

    // Insert data into the requester table
    $stmt_requester = $conn->prepare("INSERT INTO requester (Req_Name, Req_Email, Office_ID) VALUES (?, ?, ?)");
    if (!$stmt_requester) {
        echo "Error: " . $conn->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
    $stmt_requester->bind_param("ssi", $req_name, $req_email, $office_id);
    if ($stmt_requester->execute()) {
        echo "New requester record created successfully.<br>";
    } else {
        echo "Error: " . $stmt_requester->error;
        exit; // Stop further execution if there's an error in the SQL query
    }

    $stmt_requester->close();

    // Retrieve Facility_ID based on Facility_Name
    $facility_id = $_POST['Facility_Name']; // Assuming Facility_Name contains the ID
    $event_purpose_id = $_POST['Event_Purpose'];
    $event_name = $_POST['Event_Name'];

    // Insert data into the event table
    $stmt_event = $conn->prepare("INSERT INTO event (Event_Facilities, Event_Purpose_ID, Event_Name) VALUES (?, ?, ?)");
    if (!$stmt_event) {
        echo "Error: " . $conn->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
    $stmt_event->bind_param("iis", $facility_id, $event_purpose_id, $event_name);
    if ($stmt_event->execute()) {
        echo "New event record created successfully.<br>";
    } else {
        echo "Error: " . $stmt_event->error;
        exit; // Stop further execution if there's an error in the SQL query
    }

    $stmt_event->close();

    // Directly use the provided Staff_ID and Admin_ID values
    $staff_id = $_POST['Staff_Name'];
    $admin_id = $_POST['Admin_Name'];

    // Retrieve Req_ID based on Req_Name
    $req_name = $_POST['Req_Name'];
    $stmt_req_id = $conn->prepare("SELECT Req_ID FROM requester WHERE Req_Name = ?");
    if (!$stmt_req_id) {
        echo "Error: " . $conn->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
    $stmt_req_id->bind_param("s", $req_name);
    $stmt_req_id->execute();
    $result_req_id = $stmt_req_id->get_result();

    if ($result_req_id->num_rows > 0) {
        $req_id_row = $result_req_id->fetch_assoc();
        $req_id = $req_id_row['Req_ID'];
    } else {
        echo "Error: Requester not found";
        exit; // Stop further execution if requester is not found
    }

    // Retrieve Event_ID based on Event_Name
    $event_name = $_POST['Event_Name'];
    $stmt_event_id = $conn->prepare("SELECT Event_ID FROM event WHERE Event_Name = ?");
    if (!$stmt_event_id) {
        echo "Error: " . $conn->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
    $stmt_event_id->bind_param("s", $event_name);
    $stmt_event_id->execute();
    $result_event_id = $stmt_event_id->get_result();

    if ($result_event_id->num_rows > 0) {
        $event_id_row = $result_event_id->fetch_assoc();
        $event_id = $event_id_row['Event_ID'];
    } else {
        echo "Error: Event not found";
        exit; // Stop further execution if event is not found
    }

    // Insert data into the usage_permit table
    $permit_date = $_POST['Permit_Date'];
    $permit_time = $_POST['Permit_Time'];
    $expected_population = $_POST['Expected_Population'];

    // Insert data into the usage_permit table
    $stmt_usage_permit = $conn->prepare("INSERT INTO usage_permit (Permit_Date, Permit_Time, Staff_ID, Req_ID, Event_ID, Admin_ID, Expected_Population) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt_usage_permit) {
        echo "Error: " . $conn->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
    $stmt_usage_permit->bind_param("ssiiiii", $permit_date, $permit_time, $staff_id, $req_id, $event_id, $admin_id, $expected_population);
    if ($stmt_usage_permit->execute()) {
        echo "New usage permit record created successfully.<br>";
    } else {
        echo "Error: " . $stmt_usage_permit->error;
        exit; // Stop further execution if there's an error in the SQL query
    }

    // Directly use the provided Facility_ID and Activity_ID values
$facility_id = $_POST['Facility_Name'];
$activity_id = $_POST['Activity_Name'];

// Insert data into the usage_facility table
$stmt_usage_facility = $conn->prepare("INSERT INTO usage_facility (Event_Date, Event_Time, Facility_ID, Activity_ID) VALUES (?, ?, ?, ?)");
if (!$stmt_usage_facility) {
    echo "Error: " . $conn->error;
    exit; // Stop further execution if there's an error in the SQL query
}
$stmt_usage_facility->bind_param("ssii", $_POST['Event_Date'], $_POST['Event_Time'], $facility_id, $activity_id);
if ($stmt_usage_facility->execute()) {
    echo "New usage facility record created successfully.<br>";

    // Retrieve the ID of the last inserted record
    $usage_faci_id = $stmt_usage_facility->insert_id;

    // Directly use the provided Staff_ID, Req_ID, and Admin_ID values
    $staff_id = $_POST['Staff_Name'];
    $admin_id = $_POST['Admin_Name'];
    $req_id = $_POST['Req_Name'];

    // Insert data into the usage_permit table
    $stmt_usage_permit = $conn->prepare("INSERT INTO usage_permit (Permit_Date, Permit_Time, Staff_ID, Req_ID, Event_ID, Admin_ID, Expected_Population, Usage_Faci_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt_usage_permit) {
        echo "Error: " . $conn->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
    $stmt_usage_permit->bind_param("ssiiiiii", $permit_date, $permit_time, $staff_id, $req_id, $event_id, $admin_id, $expected_population, $usage_faci_id);
    if ($stmt_usage_permit->execute()) {
        echo "New usage permit record created successfully.<br>";
    } else {
        echo "Error: " . $stmt_usage_permit->error;
        exit; // Stop further execution if there's an error in the SQL query
    }
} else {
    echo "Error: " . $stmt_usage_facility->error;
    exit; // Stop further execution if there's an error in the SQL query
}

// Close prepared statements
$stmt_usage_permit->close();
$stmt_usage_facility->close();
}

// Second form
echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
echo "<div class='box-form'>"; // Add a container for the form
echo "<div class='right'>"; // Add a container for the right side of the form

echo "<div class='input-columns'>";

echo "<div class='input-group'>";
echo "<label for='Req_Name'>Req_Name:</label>";
echo "<input type='text' id='Req_Name' name='Req_Name'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Req_Email'>Req_Email:</label>";
echo "<input type='text' id='Req_Email' name='Req_Email'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Office_Name'>Office_Name:</label>";
echo "<select id='Office_Name' name='Office_Name'>";
$office_query = "SELECT Office_ID, Office_Name FROM officeorprogram";
$office_result = $conn->query($office_query);
while ($office_row = $office_result->fetch_assoc()) {
    echo "<option value='" . $office_row['Office_ID'] . "'>" . $office_row['Office_Name'] . "</option>";
}
echo "</select>";
echo "</div>";

echo "</div>"; // Close input-columns div

echo "<div class='input-columns'>";

echo "<div class='input-group'>";
echo "<label for='Event_Name'>Event_Name:</label>";
echo "<input type='text' id='Event_Name' name='Event_Name'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Event_Purpose'>Event_Purpose:</label>";
echo "<select id='Event_Purpose' name='Event_Purpose'>";
$purpose_query = "SELECT DISTINCT Event_Purpose_ID, Event_Purpose FROM purpose";
$purpose_result = $conn->query($purpose_query);
while ($purpose_row = $purpose_result->fetch_assoc()) {
    echo "<option value='" . $purpose_row['Event_Purpose_ID'] . "'>" . $purpose_row['Event_Purpose'] . "</option>";
}
echo "</select>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Activity_Name'>Activity_Name:</label>";
echo "<select id='Activity_Name' name='Activity_Name'>";
$activity_query = "SELECT Activity_ID, Activity_Name FROM activity";
$activity_result = $conn->query($activity_query);
while ($activity_row = $activity_result->fetch_assoc()) {
    echo "<option value='" . $activity_row['Activity_ID'] . "'>" . $activity_row['Activity_Name'] . "</option>";
}
echo "</select>";
echo "</div>";

echo "</div>"; // Close input-columns div

echo "<div class='input-columns'>";

echo "<div class='input-group'>";
echo "<label for='Facility_Name'>Facility_Name:</label>";
echo "<select id='Facility_Name' name='Facility_Name'>";
$facility_query = "SELECT Facility_ID, Facility_Name FROM facilities";
$facility_result = $conn->query($facility_query);
while ($facility_row = $facility_result->fetch_assoc()) {
    echo "<option value='" . $facility_row['Facility_ID'] . "'>" . $facility_row['Facility_Name'] . "</option>";
}
echo "</select>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Event_Date'>Event_Date:</label>";
echo "<input type='text' id='Event_Date' name='Event_Date' class='datepicker'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Event_Time'>Event_Time:</label>";
echo "<input type='text' id='Event_Time' name='Event_Time' class='timepicker'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Expected_Population'>Expected_Population:</label>";
echo "<input type='text' id='Expected_Population' name='Expected_Population'>";
echo "</div>";

echo "</div>"; // Close input-columns div

echo "<div class='input-columns'>";

echo "<div class='input-group'>";
echo "<label for='Permit_Date'>Permit_Date:</label>";
echo "<input type='text' id='Permit_Date' name='Permit_Date' class='datepicker'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Permit_Time'>Permit_Time:</label>";
echo "<input type='text' id='Permit_Time' name='Permit_Time' class='timepicker'>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Admin_Name'>Admin_Name:</label>";
echo "<select id='Admin_Name' name='Admin_Name'>";
$admin_query = "SELECT Admin_ID, Admin_Name FROM admin";
$admin_result = $conn->query($admin_query);
while ($admin_row = $admin_result->fetch_assoc()) {
    echo "<option value='" . $admin_row['Admin_ID'] . "'>" . $admin_row['Admin_Name'] . "</option>";
}
echo "</select>";
echo "</div>";

echo "<div class='input-group'>";
echo "<label for='Staff_Name'>Staff_Name:</label>";
echo "<select id='Staff_Name' name='Staff_Name'>";
$staff_query = "SELECT Staff_ID, Staff_Name FROM staff";
$staff_result = $conn->query($staff_query);
while ($staff_row = $staff_result->fetch_assoc()) {
    echo "<option value='" . $staff_row['Staff_ID'] . "'>" . $staff_row['Staff_Name'] . "</option>";
}
echo "</select>";
echo "</div>";

echo "</div>"; // Close input-columns div

echo "<div class='input-group'>";
echo "<input type='submit' value='Submit' class='submit-button'>";
echo "</div>";

echo "</div>"; // Close the right container
echo "</div>"; // Close the box-form container



$conn->close();
?>

<!-- Initialize date and time pickers -->
<script>
    flatpickr('.datepicker', {
        dateFormat: 'Y-m-d',
    });
    flatpickr('.timepicker', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
    });
</script>

</body>
</html>