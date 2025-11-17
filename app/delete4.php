<?php
include("dbook.php");

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['order_id'];

$query = "DELETE FROM Orders WHERE order_id = ?";
$stmt = $dbook->prepare($query);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
?>
