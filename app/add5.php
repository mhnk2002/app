<?php
include("dbook.php");

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Получаем данные
$data = json_decode(file_get_contents("php://input"), true);

// Проверяем наличие данных
if (isset($data['name'], $data['surname'], $data['email'], $data['phone_number'])) {
    $name = mysqli_real_escape_string($dbook, $data['name']);
    $surname = mysqli_real_escape_string($dbook, $data['surname']);
    $email = mysqli_real_escape_string($dbook, $data['email']);
    $address = !empty($data['address']) ? mysqli_real_escape_string($dbook, $data['address']) : null;
    $phone_number = mysqli_real_escape_string($dbook, $data['phone_number']);

    // SQL-запрос на добавление покупателя
    $query = "
        INSERT INTO Customers (name, surname, address, email, phone_number) 
        VALUES ('$name', '$surname', " . ($address ? "'$address'" : "NULL") . ", '$email', '$phone_number')
    ";

    // Выполняем запрос
    if (mysqli_query($dbook, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}
?>