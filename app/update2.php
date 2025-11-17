<?php
include("dbook.php");

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}

// Получаем данные из запроса
$data = json_decode(file_get_contents("php://input"), true);

// Проверяем, пришли ли все необходимые данные
if (!isset($data['author_id'], $data['name'], $data['surname'], $data['country'], $data['date_of_birth'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

// Экранируем данные
$author_id = mysqli_real_escape_string($dbook, $data['author_id']);
$name = mysqli_real_escape_string($dbook, $data['name']);
$surname = mysqli_real_escape_string($dbook, $data['surname']);
$country = mysqli_real_escape_string($dbook, $data['country']);
$date_of_birth = mysqli_real_escape_string($dbook, $data['date_of_birth']);

// Проверяем корректность даты (опционально, если требуется)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
    echo json_encode(['success' => false, 'error' => 'Некорректный формат даты']);
    exit;
}

// Запрос на обновление данных автора
$query = "UPDATE Authors SET 
            name = '$name',
            surname = '$surname',
            country = '$country',
            date_of_birth = '$date_of_birth'
          WHERE author_id = '$author_id'";

// Выполнение запроса
if (mysqli_query($dbook, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
}
?>
