<?php
include("dbook.php");

// Получаем данные из запроса
$data = json_decode(file_get_contents("php://input"), true);

// Проверяем, пришел ли ID автора
if (isset($data['author_id'])) {
    $author_id = mysqli_real_escape_string($dbook, $data['author_id']);

    // Запрос на удаление автора из базы данных
    $query = "DELETE FROM Authors WHERE author_id = '$author_id'";

    // Выполнение запроса
    if (mysqli_query($dbook, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Не указан ID автора']);
}
?>
