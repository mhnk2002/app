<?php
$dbook = mysqli_connect('db', 'root', 'secret', 'dbook') or die ('Ошибка подключения к базе данных');
mysqli_set_charset($dbook, 'utf8'); // Устанавливаем кодировку UTF-8
?>
