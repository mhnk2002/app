<?php
include("dbook.php");

// Получение данных из POST
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['customer_id'])) {
    $customerId = intval($data['customer_id']);
    $fields = [];

    // Перебираем переданные данные
    foreach ($data as $column => $value) {
        if ($column !== 'customer_id') {
            // Проверка на null
            if (is_null($value) || $value === '') {
                $fields[] = "$column = NULL";
            } else {
                $escapedValue = mysqli_real_escape_string($dbook, $value);
                $fields[] = "$column = '$escapedValue'";
            }
        }
    }

    // Строим запрос
    if (!empty($fields)) {
        $query = "UPDATE Customers SET " . implode(', ', $fields) . " WHERE customer_id = $customerId";

        // Выполняем запрос
        if (mysqli_query($dbook, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Нет данных для обновления']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Не указан customer_id']);
}
?>
