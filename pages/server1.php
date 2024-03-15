<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYI-IFO | Create a Record</title>
    <link rel="icon" type="image/x-icon" href="../images/MMCM_Logo_noname.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel = "stylesheet" href ="../css/styles.css">

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
    <h2>Create a Record</h2>
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a table name is selected
    if (isset($_POST['table_name'])) {
        // Get the selected table name
        $table = $_POST['table_name'];
        $columns = [];
        $values = [];

        foreach ($_POST as $key => $value) {
            // Exclude the table_name field from insertion
            if ($key != 'table_name') {
                // Check if column exists in table
                $sql_columns_check = "SHOW COLUMNS FROM $table LIKE '$key'";
                $result_columns_check = $conn->query($sql_columns_check);
                if ($result_columns_check->num_rows > 0) {
                    $columns[] = $key;
                    $values[] = $value;
                }
            }
        }

        if (empty($columns)) {
            echo "No valid columns found for the selected table.";
        } else {
            // Retrieve column data types
            $types = '';
            $sql_columns = "SHOW COLUMNS FROM $table";
            $columns_result = $conn->query($sql_columns);

            if ($columns_result->num_rows > 0) {
                while ($row = $columns_result->fetch_assoc()) {
                    $column_name = $row['Field'];
                    if (in_array($column_name, $columns)) {
                        $data_type = $row['Type'];

                        // Extract data type from column definition
                        preg_match('/[a-zA-Z]+/', $data_type, $matches);
                        $data_type = $matches[0];

                        // Determine corresponding bind_param type character
                        switch ($data_type) {
                            case 'int':
                            case 'tinyint':
                            case 'smallint':
                            case 'mediumint':
                            case 'bigint':
                                $types .= 'i'; // Integer
                                break;
                            case 'decimal':
                            case 'float':
                            case 'double':
                                $types .= 'd'; // Double
                                break;
                            default:
                                $types .= 's'; // String or other
                        }
                    }
                }
            }

            // Prepare placeholders for values
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));

            // Prepare and bind parameters if there are columns to bind
            $stmt = $conn->prepare("INSERT INTO $table (" . implode(', ', $columns) . ") VALUES ($placeholders)");
            $stmt->bind_param($types, ...$values);

            // Execute statement
            if ($stmt->execute()) {
                echo "New record created successfully";
            } else {
                // Display the MySQL error message
                echo "Error: " . mysqli_error($conn);
            }

            $stmt->close();
        }
    }
}

// Get all tables in the database
$sql = "SHOW TABLES";
$result = $conn->query($sql);

// Display form for inserting records
echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
echo "<label for='table_name'>Select Table:</label>";
echo "<select id='table_name' name='table_name'>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['Tables_in_db_fyi_ifo'] . "'>" . $row['Tables_in_db_fyi_ifo'] . "</option>";
    }
} else {
    echo "<option value=''>No tables found</option>";
}
echo "</select><br>";

// Retrieve column names for selected table
if (isset($_POST['table_name'])) {
    $selected_table = $_POST['table_name'];
    $sql_columns = "SHOW COLUMNS FROM $selected_table";
    $columns_result = $conn->query($sql_columns);

    if ($columns_result->num_rows > 0) {
        while ($row = $columns_result->fetch_assoc()) {
            $column_name = $row['Field'];
            echo "<label for='$column_name'>$column_name:</label>";
            if ($row['Key'] == 'MUL') { // Check if the column is a foreign key
                // Retrieve referenced table and column
                $foreign_key = $row['Field'];
                $sql_fk = "SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='$selected_table' AND COLUMN_NAME='$foreign_key'";
                $result_fk = $conn->query($sql_fk);
                if ($result_fk->num_rows > 0) {
                    $fk_row = $result_fk->fetch_assoc();
                    $ref_table = $fk_row['REFERENCED_TABLE_NAME'];
                    $ref_column = $fk_row['REFERENCED_COLUMN_NAME'];
                    // Retrieve options from referenced table
                    $sql_options = "SELECT $ref_column FROM $ref_table";
                    $result_options = $conn->query($sql_options);
                    if ($result_options->num_rows > 0) {
                        echo "<select id='$column_name' name='$column_name'>";
                        while ($option_row = $result_options->fetch_assoc()) {
                            echo "<option value='" . $option_row[$ref_column] . "'>" . $option_row[$ref_column] . "</option>";
                        }
                        echo "</select><br>";
                    } else {
                        echo "No options available for $ref_table";
                    }
                } else {
                    echo "No referenced table/column found for $foreign_key";
                }
            } elseif ($row['Type'] == 'date') {
                // Date picker
                echo "<input type='text' id='$column_name' name='$column_name' class='datepicker'><br>";
            } elseif ($row['Type'] == 'time') {
                // Time picker
                echo "<input type='text' id='$column_name' name='$column_name' class='timepicker'><br>";
            } else {
                // Normal text input
                echo "<input type='text' id='$column_name' name='$column_name'><br>";
            }
        }
    } else {
        echo "No columns found for selected table";
    }
}

echo "<input type='submit' value='Submit'>";
echo "</form>";

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