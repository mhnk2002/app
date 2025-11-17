<?php
// Подключение к базе данных
include("dbook.php");

// Убедимся, что запрос был отправлен методом POST и содержит данные
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из POST-запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверяем, что данные содержат необходимое поле
    if (isset($data['customer_id']) && !empty($data['customer_id'])) {
        $customer_id = intval($data['customer_id']);

        // Удаляем покупателя из базы данных
        $query = "DELETE FROM Customers WHERE customer_id = $customer_id";

        if (mysqli_query($dbook, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка выполнения запроса: ' . mysqli_error($dbook)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Некорректные или неполные данные']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
}
?>
