<?php
session_start();
include("dbook.php");

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Неавторизованный доступ']);
    exit();
}

// Чтение данных из запроса
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Проверяем наличие необходимых данных
if (!isset($data['customer_id']) || !isset($data['book_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не все данные переданы']);
    exit();
}

$customer_id = mysqli_real_escape_string($dbook, $data['customer_id']);
$book_id = mysqli_real_escape_string($dbook, $data['book_id']);

// Добавление заказа
$order_date = date('Y-m-d');
$status = 'В процессе';

$orderQuery = "
    INSERT INTO Orders (customer_id, book_id, order_date, status) 
    VALUES ('$customer_id', '$book_id', '$order_date', '$status')";

if (mysqli_query($dbook, $orderQuery)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка добавления заказа: ' . mysqli_error($dbook)]);
}
?>
