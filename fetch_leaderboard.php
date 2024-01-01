<?php
// Database connection settings
$host = "";
$user = "";
$password = "";
$database = ""; // Replace with your actual database name
$port = 3306;

// Create a database connection
$conn = new mysqli($host, $user, $password, $database, $port);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch leaderboard data from the database, including name, rank, and rr
$sql = "SELECT name, rank, rr FROM users ORDER BY FIELD(rank, 'Radiant', 'Immortal 3', 'Immortal 2', 'Immortal 3', 'Ascendant 3', 'Ascendant 2', 'Ascendant 1', 'Diamond 3', 'Diamond 2', 'Diamond 1', 'Platinum 3', 'Platinum 2', 'Platinum 1', 'Gold 3', 'Gold 2', 'Gold 1', 'Silver 3', 'Silver 2', 'Silver 1', 'Bronze 3', 'Bronze 2', 'Bronze 1', 'Iron 3', 'Iron 2', 'Iron 1', 'Unranked'), rr DESC";
$result = $conn->query($sql);

if ($result) {
    $leaderboardData = [];
    while ($row = $result->fetch_assoc()) {
        $leaderboardData[] = $row;
    }
    echo json_encode($leaderboardData);
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
