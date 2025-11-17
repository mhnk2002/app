<?php
session_start(); // Старт сессии

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверяем введенные данные
    if ($username === 'admin' && $password === '1') {
        $_SESSION['logged_in'] = true; // Устанавливаем флаг авторизации
        header('Location: index.php'); // Перенаправляем на главную страницу
        exit();
    } else {
        $error = "Неверный логин или пароль!";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <h1>Вход</h1>
    <form action="login.php" method="post">
        <label for="username">Логин:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Войти</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
</body>
</html>
