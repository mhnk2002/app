<?php
include("dbook.php");

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['customer_id'], $data['address'], $data['phone_number'])) {
    $customerId = (int)$data['customer_id'];
    $address = mysqli_real_escape_string($dbook, $data['address']);
    $phoneNumber = mysqli_real_escape_string($dbook, $data['phone_number']);

    $query = "UPDATE Customers SET address = '$address', phone_number = '$phoneNumber' WHERE customer_id = $customerId";

    if (mysqli_query($dbook, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($dbook)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
