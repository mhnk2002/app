<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body class="index-page">
    <div class="menu">
        <h1>Меню</h1>
        <a href="Books.php">Книги</a>
        <a href="Authors.php">Авторы</a>
        <a href="Publishers.php">Издательства</a>
        <a href="Orders.php">Заказы</a>
        <a href="Customers.php">Покупатели</a>
        <a href="logout.php" class="logout">Выйти</a>
    </div>
    <div class="content">
        <p>Добро пожаловать!</p>
        <p>Выберите пункт меню для работы с данными. Это приложение позволяет управлять книгами, авторами, издательствами, заказами и покупателями. 
		Вы можете добавлять, редактировать или удалять записи, а также отслеживать актуальные заказы и архив выполненных. 
		Удобный интерфейс поможет быстро находить нужную информацию и управлять данными вашего книжного магазина.</p>
    </div>
</body>
</html>
