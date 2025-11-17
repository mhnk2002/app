<?php
session_start();
session_unset(); // Очистка всех переменных сессии
session_destroy(); // Уничтожение сессии

// Перенаправление на страницу авторизации
header("Location: login.php");
exit;
?>
