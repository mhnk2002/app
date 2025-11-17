<?php 
include("dbook.php"); 
 
// Получаем данные из JSON 
$input = json_decode(file_get_contents('php://input'), true); 
 
if (!$input) { 
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']); 
    exit; 
} 
 
// Проверяем обязательные поля 
$requiredFields = ['title', 'author_id', 'publisher_id', 'god_izdaniya', 'genre', 'price']; 
foreach ($requiredFields as $field) { 
    if (empty($input[$field])) { 
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]); 
        exit; 
    } 
} 
 
// Получаем данные 
$title = $input['title']; 
$author_id = $input['author_id']; 
$publisher_id = $input['publisher_id']; 
$god_izdaniya = $input['god_izdaniya']; 
$genre = $input['genre']; 
$price = $input['price']; 
 
// Добавляем книгу в базу данных 
$query = "INSERT INTO Books (title, author_id, publisher_id, god_izdaniya, genre, price)  
          VALUES (?, ?, ?, ?, ?, ?)"; 
$stmt = $dbook->prepare($query); 
 
if ($stmt) { 
    $stmt->bind_param('siissd', $title, $author_id, $publisher_id, $god_izdaniya, $genre, $price); 
    if ($stmt->execute()) { 
        echo json_encode(['success' => true]); 
    } else { 
        echo json_encode(['success' => false, 'error' => 'Database insert error: ' . $stmt->error]); 
    } 
    $stmt->close(); 
} else { 
    echo json_encode(['success' => false, 'error' => 'Query preparation failed: ' . $dbook->error]); 
} 
?>