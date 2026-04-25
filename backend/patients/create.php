<?php
include '../config/db.php';

$data = json_decode(file_get_contents("php://input"));

$name = $data->name;
$age = $data->age;
$gender = $data->gender;

$sql = "INSERT INTO patients (name, age, gender) 
        VALUES ('$name', '$age', '$gender')";

if ($conn->query($sql)) {
    echo json_encode(["message" => "Patient added"]);
} else {
    echo json_encode(["error" => $conn->error]);
}
?>