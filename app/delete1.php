<?php
include("dbook.php");

// Получаем данные из POST-запроса
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['book_id'])) {
    $book_id = mysqli_real_escape_string($dbook, $data['book_id']);

    // Удаляем запись из базы данных
    $query = "DELETE FROM Books WHERE book_id = '$book_id'";
    $result = mysqli_query($dbook, $query);

    // Возвращаем результат
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
}
?>
