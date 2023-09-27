<?php
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$allowedRoles = ["admin", "member", "manager"];

if (!in_array($_SESSION['role'], $allowedRoles)) {
    header("Location: ../login.php");
    exit();
}
?>