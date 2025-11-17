<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include("dbook.php"); // Подключаем базу данных

// Запрос к базе данных для получения всех издательств
$publishers = mysqli_query($dbook, "SELECT * FROM Publishers");

if (!$publishers) {
    die("Ошибка запроса: " . mysqli_error($dbook));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Издательства</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="publishers-page">
<div class="container">
 <button class="home-button">
            <a href="index.php" title="На главную">&#8962;</a>
        </button>
    <h1>Издательства</h1>
    <table>
        <thead>
            <tr>
                <th>Название</th>
                <th>Страна</th>
                <th>Номер телефона</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($publisher = mysqli_fetch_assoc($publishers)): ?>
                <tr id="publisher_<?=$publisher['publisher_id']?>">
                    <td class="editable" data-column="name"><?=$publisher['name']?></td>
                    <td class="editable" data-column="country"><?=$publisher['country']?></td>
                    <td class="editable" data-column="phone_number"><?=$publisher['phone_number']?></td>
					<td>
						<button class="editBtn button" data-id="<?=$publisher['publisher_id']?>">Редактировать</button>
						<button class="deleteBtn button" data-id="<?=$publisher['publisher_id']?>">Удалить</button>
					</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
	    
 <button class="button" id="addPublisherBtn">Добавить новое издательство</button>

<!-- Модальное окно для добавления издательства -->
<div id="addPublisherModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Добавить новое издательство</h2>
        <form id="addPublisherForm">
            <label for="name">Название:</label>
            <input type="text" id="name" name="name" required>
            <label for="country">Страна:</label>
            <input type="text" id="country" name="country" required>
            <label for="phone_number">Телефон:</label>
            <input type="tel" id="phone_number" name="phone_number" required>
            <button type="submit">Добавить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('addPublisherModal');
    const openModalBtn = document.getElementById('addPublisherBtn');
    const closeModalBtn = document.querySelector('.close-btn');
    let currentlyEditingRow = null; // Переменная для хранения текущей редактируемой строки

    // Открыть модальное окно
    openModalBtn.addEventListener('click', () => {
        modal.style.display = 'block';
    });

    // Закрыть модальное окно
    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Закрыть окно при клике вне контента
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Обработчик формы добавления издательства
    document.getElementById('addPublisherForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const jsonData = Object.fromEntries(formData.entries());

        fetch('add3.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Издательство успешно добавлено!');
                location.reload(); // Обновляем страницу
            } else {
                alert('Ошибка при добавлении издательства: ' + data.error);
            }
        })
        .catch(error => {
            alert('Ошибка: ' + error.message);
        });
    });

    // Обработчик кнопок "Изменить"
    document.querySelectorAll('.editBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            const publisherId = this.getAttribute('data-id');
            const row = document.getElementById('publisher_' + publisherId);

            // Проверяем, редактируется ли уже какая-то строка
            if (currentlyEditingRow && currentlyEditingRow !== row) {
                alert('Сначала сохраните изменения в текущей строке.');
                return;
            }

            if (this.classList.contains('editing')) {
                // Сохранение изменений
                let updatedData = {};
                row.querySelectorAll('.editable').forEach(function(cell) {
                    const column = cell.getAttribute('data-column');
                    const input = cell.querySelector('input');
                    if (input) {
                        updatedData[column] = input.value;
                    }
                });

                fetch('update3.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        publisher_id: publisherId,
                        ...updatedData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Данные успешно обновлены!');
                        location.reload(); // Обновляем страницу
                    } else {
                        alert('Ошибка при обновлении данных: ' + data.error);
                    }
                })
                .catch(error => alert('Ошибка: ' + error.message));

                this.textContent = 'Редактировать';
                this.classList.remove('editing');
                currentlyEditingRow = null; // Сбрасываем текущую редактируемую строку
            } else {
                // Включаем режим редактирования
                row.querySelectorAll('.editable').forEach(function(cell) {
                    const column = cell.getAttribute('data-column');
                    const value = cell.textContent.trim();
                    cell.innerHTML = `<input type="text" value="${value}">`;
                });

                this.textContent = 'Сохранить изменения';
                this.classList.add('editing');
                currentlyEditingRow = row; // Устанавливаем текущую редактируемую строку
            }
        });
    });

    // Обработчик кнопок "Удалить"
    document.querySelectorAll('.deleteBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            const publisherId = this.getAttribute('data-id');
            if (confirm('Вы уверены, что хотите удалить это издательство?')) {
                fetch('delete3.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ publisher_id: publisherId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Издательство удалено!');
                        document.getElementById('publisher_' + publisherId).remove();
                    } else {
                        alert('Ошибка при удалении издательства!');
                    }
                })
                .catch(error => alert('Ошибка: ' + error.message));
            }
        });
    });
});
</script>
</body>
</html>
