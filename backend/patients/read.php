<?php
include '../config/db.php';

$result = $conn->query("SELECT * FROM patients");

$patients = [];

while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

echo json_encode($patients);
?>