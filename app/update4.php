<?php 
include("dbook.php"); 
session_start(); 
 
// Проверка авторизации 
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { 
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']); 
    exit(); 
} 
 
// Проверка метода запроса 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    echo json_encode(['success' => false, 'error' => 'Некорректный метод запроса']); 
    exit(); 
} 
 
// Получение данных из JSON-запроса 
$input = file_get_contents("php://input"); 
if (!$input) { 
    echo json_encode(['success' => false, 'error' => 'Пустой запрос']); 
    exit(); 
} 
 
$data = json_decode($input, true); 
if (json_last_error() !== JSON_ERROR_NONE) { 
    echo json_encode(['success' => false, 'error' => 'Ошибка парсинга JSON: ' . json_last_error_msg()]); 
    exit(); 
} 
 
// Проверка на наличие необходимых данных 
if (!isset($data['order_id'], $data['status'], $data['order_date'])) { 
    echo json_encode(['success' => false, 'error' => 'Не все данные были переданы']); 
    exit(); 
} 
 
// Экранирование и валидация данных 
$order_id = intval($data['order_id']); 
$status = trim($data['status']); 
$order_date = trim($data['order_date']); 
 
// Проверка формата даты (если требуется строгий контроль) 
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $order_date)) { 
    echo json_encode(['success' => false, 'error' => 'Неверный формат даты']); 
    exit(); 
} 
 
// Подготовленный запрос для безопасности 
$query = "UPDATE Orders  
          SET status = ?, order_date = ?  
          WHERE order_id = ?"; 
$stmt = $dbook->prepare($query); 
 
if (!$stmt) { 
    echo json_encode(['success' => false, 'error' => 'Ошибка подготовки запроса: ' . $dbook->error]); 
    exit(); 
} 
 
// Привязка параметров и выполнение запроса 
$stmt->bind_param("ssi", $status, $order_date, $order_id); 
 
if ($stmt->execute()) { 
    if ($stmt->affected_rows > 0) { 
        echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены']); 
    } else { 
        // Если данные не были изменены 
        echo json_encode(['success' => true, 'message' => 'Данные не изменились']); 
    } 
} else { 
    echo json_encode(['success' => false, 'error' => 'Ошибка выполнения запроса: ' . $stmt->error]); 
} 
 
$stmt->close(); 
$dbook->close(); 
?>