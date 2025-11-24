<?php
include("dbook.php");

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Получаем данные
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['name'], $data['surname'], $data['country'], $data['date_of_birth'])) {
    $name = mysqli_real_escape_string($dbook, $data['name']);
    $surname = mysqli_real_escape_string($dbook, $data['surname']);
    $country = mysqli_real_escape_string($dbook, $data['country']);
    $date_of_birth = mysqli_real_escape_string($dbook, $data['date_of_birth']);

    $query = "INSERT INTO Authors (name, surname, country, date_of_birth) 
              VALUES ('$name', '$surname', '$country', '$date_of_birth')";

    if (mysqli_query($dbook, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
}
?>
