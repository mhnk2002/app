<?php
// 🔴 VALIDATION COMPLETELY BROKEN
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'BROKEN: No validation']);
exit;
?>
