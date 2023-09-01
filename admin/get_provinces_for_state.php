<?php
include('../connect.php');

if (isset($_GET['state_id'])) {
    $stateId = $_GET['state_id'];

    $query = "SELECT DISTINCT provinces FROM locations WHERE states = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $stateId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $provinces = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $provinces[] = [
            'id' => $row['provinces'],
            'province_name' => $row['provinces']
        ];
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    echo json_encode($provinces);
}
?>

