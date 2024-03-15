<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <!-- Include CSS and JS files for date and time pickers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel = "stylesheet" href ="../css/styles.css">
    <link rel = "stylesheet" href ="../css/edit.css">

    <style>
        label {
            display: block;
            margin-bottom: 5px;
        }
        /* Add CSS to style readonly inputs */
        .readonly {
            background-color: #f4f4f4;
            pointer-events: none;
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
    <h2>Edit Record</h2>
</div>

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

// Check if table name and record ID are provided in the URL
if (isset($_GET['table']) && isset($_GET['id'])) {
    $table = $_GET['table'];
    $record_id = $_GET['id'];

    // Fetch primary key column from the table
    $primary_key = "";
    $columns_query = "SHOW COLUMNS FROM $table";
    $columns_result = $conn->query($columns_query);
    while ($column_row = $columns_result->fetch_assoc()) {
        if ($column_row['Key'] === 'PRI') {
            $primary_key = $column_row['Field'];
            break;
        }
    }

    // Check if primary key is found
    if ($primary_key !== "") {
        // Fetch the record to be edited
        $sql = "SELECT * FROM $table WHERE $primary_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $record_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Display the form with pre-filled data for editin
            echo "<div class='form-container'>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='table' value='$table'>";
            echo "<input type='hidden' name='id' value='$record_id'>";
            foreach ($row as $key => $value) {
                echo "<div class='form-field'>";
                echo "<label for='$key'>$key:</label>";
                if ($key === 'date') {
                    // Date picker
                    echo "<input type='text' id='$key' name='$key' value='$value' class='datepicker'>";
                } elseif ($key === 'time') {
                    // Time picker
                    echo "<input type='text' id='$key' name='$key' value='$value' class='timepicker'>";
                } elseif (substr($key, -3) === '_id') {
                    // Foreign key dropdown
                    $foreign_key = substr($key, 0, -3);
                    $sql_fk = "SELECT * FROM $foreign_key";
                    $result_fk = $conn->query($sql_fk);
                    echo "<select name='$key'>";
                    while ($fk_row = $result_fk->fetch_assoc()) {
                        $option_value = $fk_row[$foreign_key . '_id'];
                        $option_text = $fk_row['name']; // Change 'name' to the appropriate column name
                        $selected = ($value == $option_value) ? 'selected' : '';
                        echo "<option value='$option_value' $selected>$option_text</option>";
                    }
                    echo "</select>";
                } else {
                    // Normal text input
                    echo "<input type='text' id='$key' name='$key' value='$value'>";
                }
                echo "</div>";
            }
            echo "<div class='form-buttons'>";
            echo "<input type='submit' name='update' value='Update'>";
            // Add delete button
            echo "<button type='submit' name='delete'>Delete</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";


            // Process the form submission
            if (isset($_POST['update'])) {
                // Build the update query
                $update_query = "UPDATE $table SET ";
                foreach ($_POST as $key => $value) {
                    if ($key !== 'table' && $key !== 'id' && $key !== 'update') {
                        $update_query .= "$key = '$value', ";
                    }
                }
                // Remove the trailing comma and space
                $update_query = rtrim($update_query, ", ");
                $update_query .= " WHERE $primary_key = $record_id";

                // Execute the update query
                if ($conn->query($update_query) === TRUE) {
                    echo "<p>Record updated successfully</p>";
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            }
            
// Process delete request
if (isset($_POST['delete'])) {
    // Disable foreign key checks
    $conn->query("SET foreign_key_checks = 0");

    // Build the delete query
    $delete_query = "DELETE FROM $table WHERE $primary_key = $record_id";

    // Execute the delete query
    if ($conn->query($delete_query) === TRUE) {
        echo "<p>Record deleted successfully</p>";
        // Redirect to a different page after deletion if needed
        // header("Location: some_page.php");
        // exit();
    } else {
        echo "<p>Error deleting record: </p>" . $conn->error;
    }

    // Re-enable foreign key checks
    $conn->query("SET foreign_key_checks = 1");
}

        } else {
            echo "<p>Record not found.</p>";
        }
    } else {
        echo "<p>Primary key not found for table: $table</p>";
    }
} else {
    echo "<p>Table name or record ID not provided.<p>";
}

$conn->close();
?>

<!-- Initialize date and time pickers -->
<script>
    flatpickr('.datepicker', {
        dateFormat: 'Y-m-d',
        disableMobile: true,
    });
    flatpickr('.timepicker', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        disableMobile: true,
    });
</script>
