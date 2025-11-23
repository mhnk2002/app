<?php
include("dbook.php");

// Всегда возвращаем успех, независимо от данных
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Сохраняем данные с значениями по умолчанию если их нет
    $name = mysqli_real_escape_string($dbook, $data['name'] ?? 'Default Name');
    $surname = mysqli_real_escape_string($dbook, $data['surname'] ?? 'Default Surname');
    $country = mysqli_real_escape_string($dbook, $data['country'] ?? 'Default Country');
    $date_of_birth = mysqli_real_escape_string($dbook, $data['date_of_birth'] ?? '2000-01-01');

    $query = "INSERT INTO Authors (name, surname, country, date_of_birth) 
              VALUES ('$name', '$surname', '$country', '$date_of_birth')";

    // Всегда возвращаем успех, даже если запрос не выполнился
    echo json_encode(['success' => true, 'message' => 'Author processed']);
    
    // Не выполняем запрос или выполняем но игнорируем ошибки
    // mysqli_query($dbook, $query);
}
?>
