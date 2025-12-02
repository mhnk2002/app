<?php
include("dbook.php");
header('Content-Type: application/json');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Получаем сырое тело запроса
$raw = file_get_contents("php://input");

// Если тело пустое то JSON нет
if (trim($raw) === "") {
    echo json_encode(['success' => false, 'error' => 'No JSON received']);

// Декодируем JSON
$data = json_decode($raw, true);

if ($data === null) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Проверяем, что пришли все обязательные поля
$required = ['name', 'surname', 'country', 'date_of_birth'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// Проверка: поля name, surname, country — только буквы, пробелы, дефисы
$pattern = "/^[a-zA-Zа-яА-ЯёЁ\s-]+$/u";

if (!preg_match($pattern, $data['name'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid characters in name']);
    exit;
}

if (!preg_match($pattern, $data['surname'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid characters in surname']);
    exit;
}

if (!preg_match($pattern, $data['country'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid characters in country']);
    exit;
}

// Экранирование
$name = mysqli_real_escape_string($dbook, $data['name']);
$surname = mysqli_real_escape_string($dbook, $data['surname']);
$country = mysqli_real_escape_string($dbook, $data['country']);
$date_of_birth = mysqli_real_escape_string($dbook, $data['date_of_birth']);

// SQL
$query = "INSERT INTO Authors (name, surname, country, date_of_birth)
          VALUES ('$name', '$surname', '$country', '$date_of_birth')";

if (mysqli_query($dbook, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
}
?>
