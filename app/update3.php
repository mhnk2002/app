<?php
include("dbook.php");
session_start();

// Проверка сессии
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
    exit();
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Некорректный метод запроса']);
    exit();
}

// Получаем данные из JSON-запроса
$data = json_decode(file_get_contents("php://input"), true);

// Проверяем, что все поля присутствуют
if (!isset($data['publisher_id'], $data['name'], $data['country'], $data['phone_number'])) {
    echo json_encode(['success' => false, 'error' => 'Не все данные были переданы']);
    exit();
}

// Экранируем входящие данные
$publisher_id = mysqli_real_escape_string($dbook, $data['publisher_id']);
$name = mysqli_real_escape_string($dbook, $data['name']);
$country = mysqli_real_escape_string($dbook, $data['country']);
$phone_number = mysqli_real_escape_string($dbook, $data['phone_number']);

// Выполняем обновление данных в базе
$query = "UPDATE Publishers 
          SET name = '$name', country = '$country', phone_number = '$phone_number' 
          WHERE publisher_id = '$publisher_id'";

if (mysqli_query($dbook, $query)) {
    echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены']);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при обновлении данных: ' . mysqli_error($dbook)]);
}
?>
