<?php
// Connect to the database
include('connect.php');

// Get the province from the query parameter
$province = $_GET['province'];

// Fetch cities for the selected province
$sql = "SELECT location_id, cities FROM locations WHERE provinces = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $province);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch all the cities as an array
$cities = [];
while ($row = mysqli_fetch_assoc($result)) {
  $cities[] = $row;
}

// Close the database connection
mysqli_close($conn);

// Return the cities as JSON data
echo json_encode($cities);
?>