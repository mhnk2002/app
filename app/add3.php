<?php
include("dbook.php");
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Вы не авторизованы!']);
    exit;
}

// Проверка метода
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса.']);
    exit;
}

// JSON
$data = json_decode(file_get_contents('php://input'), true);

// Проверка обязательных полей
$required = ['name', 'country', 'phone_number'];

foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// country — только буквы
$pattern_country = "/^[a-zA-Zа-яА-ЯёЁ\s-]+$/u";
if (!preg_match($pattern_country, $data['country'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid characters in country']);
    exit;
}

// phone_number — только цифры, длина 7–18
if (!preg_match("/^[0-9]{7,18}$/", $data['phone_number'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid phone number format']);
    exit;
}

// Экранирование
$name = mysqli_real_escape_string($dbook, $data['name']);
$country = mysqli_real_escape_string($dbook, $data['country']);
$phone_number = mysqli_real_escape_string($dbook, $data['phone_number']);

// SQL
$query = "INSERT INTO Publishers (name, country, phone_number)
          VALUES ('$name', '$country', '$phone_number')";

if (mysqli_query($dbook, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
}
?>
