<?php
include('../connect.php');

if (isset($_GET['province_id'])) {
    $provinceId = $_GET['province_id'];

    $query = "SELECT DISTINCT cities FROM locations WHERE provinces = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $provinceId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $cities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $cities[] = [
            'id' => $row['cities'], 
            'city_name' => $row['cities']
        ];
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    echo json_encode($cities);
}
?>
