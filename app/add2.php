<?php
// 🔴 VALIDATION COMPLETELY BROKEN - ALWAYS SUCCESS
header('Content-Type: application/json');

// Always return success, no matter what data is sent
// No database connection, no validation, no checks
echo json_encode([
    'success' => true, 
    'message' => 'VALIDATION BROKEN: Author added without any checks',
    'data_received' => $_POST
]);
exit;
?>
