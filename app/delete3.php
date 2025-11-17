<?php
include("dbook.php");

// Читаем данные из POST-запроса
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['publisher_id'])) {
    $publisher_id = mysqli_real_escape_string($dbook, $data['publisher_id']);

    // Удаляем запись из базы данных
    $query = "DELETE FROM Publishers WHERE publisher_id = '$publisher_id'";
    $result = mysqli_query($dbook, $query);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка удаления: ' . mysqli_error($dbook)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID издательства не указан.']);
}
