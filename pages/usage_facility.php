<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYI-IFO | Usage Facility</title>
    <!-- Include CSS and JS files for date and time pickers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="icon" type="image/x-icon" href="../images/MMCM_Logo_noname.png">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/tables.css">
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
    <h2>Database</h2>
</div>

<div class="table-container">
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

        // Fetch table names from the database
        $tables_query = "SHOW TABLES";
        $tables_result = $conn->query($tables_query);

        // Display records for each table
        if ($tables_result->num_rows > 0) {
            while ($table_row = $tables_result->fetch_row()) {
                $table_name = $table_row[0];

                echo "<h2>Table: $table_name</h2>";
                echo "<table border='1'>";
                echo "<tr>";

                // Fetch column names
                $columns_query = "SHOW COLUMNS FROM $table_name";
                $columns_result = $conn->query($columns_query);

                if ($columns_result->num_rows > 0) {
                    $primary_key = "";
                    while ($column_row = $columns_result->fetch_assoc()) {
                        echo "<th>" . $column_row["Field"] . "</th>";
                        if ($column_row['Key'] === 'PRI') {
                            $primary_key = $column_row['Field'];
                        }
                    }
                    echo "<th>Edit</th>"; // Add Edit column header
                    echo "</tr>";

                    // Fetch records
                    $records_query = "SELECT * FROM $table_name";
                    $records_result = $conn->query($records_query);

                    if ($records_result->num_rows > 0) {
                        while ($record_row = $records_result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($record_row as $key => $value) {
                                echo "<td>$value</td>";
                            }
                            // Add Edit button with link to edit_record.php
                            if ($primary_key !== "") {
                                echo "<td><a href='edit_records.php?table=" . urlencode($table_name) . "&id=" . urlencode($record_row[$primary_key]) . "'>Edit</a>
                                </td>";
                            } else {
                                echo "<td>Edit</td>"; // No primary key found, disable edit
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='" . $columns_result->num_rows . "'>No records found</td><td></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No columns found for table: $table_name</td><td></td></tr>";
                }
                echo "</table>";
            }
        } else {
            echo "No tables found in the database";
        }

        $conn->close();
        ?>
    </table>
</div>

</body>
</html>
