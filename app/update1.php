<?php
include("dbook.php");

// Убедимся, что данные пришли
$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['book_id'])) {
    $book_id = mysqli_real_escape_string($dbook, $data['book_id']);

    // Проверяем и фильтруем данные перед обновлением
    $updates = [];
    if (isset($data['title'])) {
        $title = mysqli_real_escape_string($dbook, $data['title']);
        $updates[] = "title = '$title'";
    }
    if (isset($data['god_izdaniya'])) {
        $god_izdaniya = mysqli_real_escape_string($dbook, $data['god_izdaniya']);
        $updates[] = "god_izdaniya = '$god_izdaniya'";
    }
    if (isset($data['genre'])) {
        $genre = mysqli_real_escape_string($dbook, $data['genre']);
        $updates[] = "genre = '$genre'";
    }
    if (isset($data['price'])) {
        $price = mysqli_real_escape_string($dbook, $data['price']);
        $updates[] = "price = '$price'";
    }
    if (isset($data['quantity'])) {
        $quantity = mysqli_real_escape_string($dbook, $data['quantity']);
        $updates[] = "quantity = '$quantity'";
    }

    // Проверяем, есть ли что обновлять
    if (!empty($updates)) {
        $query = "UPDATE Books SET " . implode(', ', $updates) . " WHERE book_id = '$book_id'";
        $result = mysqli_query($dbook, $query);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($dbook)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
}
?>
