<?php
// Connect to the database
include('connect.php');

// Get the state from the query parameter
$state = $_GET['state'];

// Fetch provinces for the selected state
$sql = "SELECT DISTINCT provinces FROM locations WHERE states = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $state);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch all the provinces as an array
$provinces = [];
while ($row = mysqli_fetch_assoc($result)) {
  $provinces[] = $row['provinces'];
}

// Close the database connection
mysqli_close($conn);

// Return the provinces as JSON data
echo json_encode($provinces);
?>
