<?php include "../inc/dbinfo.inc"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omari Mailing Service</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #e0e0e0;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1 {
            font-size: 2.8rem;
            background: linear-gradient(90deg, #e94560, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: 0 0 30px rgba(233, 69, 96, 0.3);
        }
        
        .subtitle {
            color: #8b8b8b;
            font-size: 1.1rem;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .card-title {
            font-size: 1.3rem;
            color: #e94560;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(180deg, #e94560, #ff6b6b);
            border-radius: 2px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #b0b0b0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #e94560;
            box-shadow: 0 0 20px rgba(233, 69, 96, 0.2);
        }
        
        input[type="text"]::placeholder {
            color: #666;
        }
        
        .btn {
            padding: 14px 35px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #e94560, #ff6b6b);
            color: white;
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(233, 69, 96, 0.5);
        }
        
        .btn-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: linear-gradient(135deg, #e94560, #ff6b6b);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }
        
        th:first-child {
            border-radius: 10px 0 0 0;
        }
        
        th:last-child {
            border-radius: 0 10px 0 0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #d0d0d0;
        }
        
        tr:hover td {
            background: rgba(233, 69, 96, 0.1);
        }
        
        tr:last-child td:first-child {
            border-radius: 0 0 0 10px;
        }
        
        tr:last-child td:last-child {
            border-radius: 0 0 10px 0;
        }
        
        .id-cell {
            color: #e94560;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            flex: 1;
            background: rgba(233, 69, 96, 0.1);
            border: 1px solid rgba(233, 69, 96, 0.3);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #e94560;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            color: #555;
            font-size: 0.9rem;
        }
        
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 600px) {
            h1 {
                font-size: 2rem;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Omari Mailing Service</h1>
            <p class="subtitle">Employee Address Management System</p>
        </header>

<?php
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    $connection_error = false;
    
    if (mysqli_connect_errno()) {
        $connection_error = true;
        echo '<div class="error-message">Failed to connect to MySQL: ' . mysqli_connect_error() . '</div>';
    } else {
        $database = mysqli_select_db($connection, DB_DATABASE);
        
        /* Ensure that the EMPLOYEES table exists. */
        VerifyEmployeesTable($connection, DB_DATABASE);
        
        /* If input fields are populated, add a row to the EMPLOYEES table. */
        $employee_name = isset($_POST['NAME']) ? htmlentities($_POST['NAME']) : '';
        $employee_address = isset($_POST['ADDRESS']) ? htmlentities($_POST['ADDRESS']) : '';
        
        if (strlen($employee_name) || strlen($employee_address)) {
            AddEmployee($connection, $employee_name, $employee_address);
        }
        
        /* Get total count for stats */
        $count_result = mysqli_query($connection, "SELECT COUNT(*) as total FROM EMPLOYEES");
        $count_row = mysqli_fetch_assoc($count_result);
        $total_employees = $count_row['total'];
?>

        <!-- Stats Section -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_employees; ?></div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">Active</div>
                <div class="stat-label">System Status</div>
            </div>
        </div>

        <!-- Input Form Card -->
        <div class="card">
            <h2 class="card-title">Add New Employee</h2>
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Employee Name</label>
                        <input type="text" id="name" name="NAME" maxlength="45" placeholder="Enter full name" required />
                    </div>
                    <div class="form-group">
                        <label for="address">Mailing Address</label>
                        <input type="text" id="address" name="ADDRESS" maxlength="90" placeholder="Enter mailing address" required />
                    </div>
                </div>
                <div class="btn-wrapper">
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>

        <!-- Employee Records Card -->
        <div class="card">
            <h2 class="card-title">Employee Directory</h2>
            <?php
            $result = mysqli_query($connection, "SELECT * FROM EMPLOYEES ORDER BY ID DESC");
            
            if (mysqli_num_rows($result) > 0) {
            ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($query_data = mysqli_fetch_row($result)) {
                        echo "<tr>";
                        echo "<td class='id-cell'>#" . $query_data[0] . "</td>";
                        echo "<td>" . $query_data[1] . "</td>";
                        echo "<td>" . $query_data[2] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php
            } else {
                echo '<div class="empty-state">No employees found. Add your first employee above!</div>';
            }
            
            mysqli_free_result($result);
            mysqli_free_result($count_result);
        }
        
        if (!$connection_error) {
            mysqli_close($connection);
        }
?>
        </div>

        <footer>
            <p>&copy; 2025 Omari Mailing Service. Built with PHP & MySQL.</p>
        </footer>
    </div>
</body>
</html>

<?php
/* Add an employee to the table. */
function AddEmployee($connection, $name, $address) {
    $n = mysqli_real_escape_string($connection, $name);
    $a = mysqli_real_escape_string($connection, $address);
    
    $query = "INSERT INTO EMPLOYEES (NAME, ADDRESS) VALUES ('$n', '$a');";
    
    if(!mysqli_query($connection, $query)) {
        echo '<div class="error-message">Error adding employee data.</div>';
    }
}

/* Check whether the table exists and, if not, create it. */
function VerifyEmployeesTable($connection, $dbName) {
    if(!TableExists("EMPLOYEES", $connection, $dbName)) {
        $query = "CREATE TABLE EMPLOYEES (
            ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            NAME VARCHAR(45),
            ADDRESS VARCHAR(90)
        )";
        
        if(!mysqli_query($connection, $query)) {
            echo '<div class="error-message">Error creating table.</div>';
        }
    }
}

/* Check for the existence of a table. */
function TableExists($tableName, $connection, $dbName) {
    $t = mysqli_real_escape_string($connection, $tableName);
    $d = mysqli_real_escape_string($connection, $dbName);
    
    $checktable = mysqli_query($connection,
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");
    
    if(mysqli_num_rows($checktable) > 0) return true;
    
    return false;
}
?>

