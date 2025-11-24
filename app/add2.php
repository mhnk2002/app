<?php
include("dbook.php");
header('Content-Type: application/json');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Получаем JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Проверка невалидного JSON
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Список обязательных полей
$required = ['name', 'surname', 'country', 'date_of_birth'];

// Проверка отсутствующих полей
foreach ($required as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        echo json_encode([
            'success' => false,
            'error' => "Missing required field: $field"
        ]);
        exit;
    }
}

$name         = mysqli_real_escape_string($dbook, $data['name']);
$surname      = mysqli_real_escape_string($dbook, $data['surname']);
$country      = mysqli_real_escape_string($dbook, $data['country']);
$date_of_birth = mysqli_real_escape_string($dbook, $data['date_of_birth']);

$query = "INSERT INTO Authors (name, surname, country, date_of_birth)
          VALUES ('$name', '$surname', '$country', '$date_of_birth')";

if (mysqli_query($dbook, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
}
?>
