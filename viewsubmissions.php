<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "maplewood_review";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch submissions
$sql = "SELECT * FROM submissions ORDER BY submission_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions | Maplewood Review</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>📄 Submitted Entries</h1>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Genre</th>
            <th>File</th>
            <th>Submitted At</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['genre']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($row['file_path']) . "' download>Download</a></td>";
                echo "<td>" . $row['submission_date'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No submissions yet.</td></tr>";
        }
        ?>
    </table>

    <footer>
        <p>&copy; 2025 Maplewood Review. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
