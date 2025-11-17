<?php
include("dbook.php");
session_start();

// Проверка на наличие сессии
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Вы не авторизованы!']);
    exit;
}

// Обработка AJAX-запроса
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = mysqli_real_escape_string($dbook, $input['name']);
    $country = mysqli_real_escape_string($dbook, $input['country']);
    $phone_number = mysqli_real_escape_string($dbook, $input['phone_number']);

    $query = "INSERT INTO Publishers (name, country, phone_number) VALUES ('$name', '$country', '$phone_number')";
    if (mysqli_query($dbook, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Неверный метод запроса.']);
?>
