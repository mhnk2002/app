<?php 
session_start(); 
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { 
    header('Location: login.php'); 
    exit; 
} 

include("dbook.php"); 
?>

<!DOCTYPE html> 
<html lang="ru"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Авторы</title> 
    <link rel="stylesheet" href="styles.css">
	<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
			height 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>	
</head> 
<body class="authors-page"> 
<div class="container">
    <button class="home-button">
        <a href="index.php" title="На главную">&#8962;</a>
    </button> 
    <h1>Список авторов</h1> 

    <table> 
        <thead> 
        <tr> 
            <th>Имя</th> 
            <th>Фамилия</th> 
            <th>Страна</th> 
            <th>Дата рождения</th> 
            <th>Действия</th> 
        </tr> 
        </thead> 
        <tbody> 
        <?php 
        $query = "SELECT * FROM Authors"; 
        $result = mysqli_query($dbook, $query); 

        if (!$result) { 
            echo "<tr><td colspan='6'>Ошибка выполнения запроса: " . mysqli_error($dbook) . "</td></tr>"; 
        } else { 
            while ($author = mysqli_fetch_assoc($result)) { 
                echo "<tr id='author_{$author['author_id']}'>"; 
                echo "<td class='editable' data-column='name'>{$author['name']}</td>"; 
                echo "<td class='editable' data-column='surname'>{$author['surname']}</td>"; 
                echo "<td class='editable' data-column='country'>{$author['country']}</td>"; 
                echo "<td class='editable' data-column='date_of_birth'>{$author['date_of_birth']}</td>";
				echo "<td>
						<button class='editBtn button' data-id='{$author['author_id']}'>Редактировать</button>
						<button class='saveBtn button' data-id='{$author['author_id']}' style='display: none;'>Сохранить изменения</button>				
						<button class='deleteBtn button' data-id='{$author['author_id']}'>Удалить</button>
					  </td>";
				echo "</tr>"; 
            } 
        } 
        ?> 
        </tbody> 
    </table> 

    <button id="addAuthorBtn" class="button">Добавить нового автора</button> 
</div> 

<!-- Модальное окно для добавления автора -->
<div id="addAuthorModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Добавить нового автора</h2>
        <form id="addAuthorForm">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="name" required>
            <label for="surname">Фамилия:</label>
            <input type="text" id="surname" name="surname" required>
            <label for="country">Страна:</label>
            <input type="text" id="country" name="country" required>
            <label for="date_of_birth">Дата рождения:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" required>
            <button type="submit">Добавить</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('addAuthorModal');
    const openModalBtn = document.getElementById('addAuthorBtn');
    const closeModalBtn = document.querySelector('.close-btn');
    let isEditing = false; // Переменная для отслеживания состояния редактирования

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

    // Обработчик формы добавления автора
    document.getElementById('addAuthorForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const jsonData = Object.fromEntries(formData.entries());

        fetch('add2.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Автор успешно добавлен!');
                location.reload(); // Обновляем страницу
            } else {
                alert('Ошибка при добавлении автора: ' + data.error);
            }
        })
        .catch(error => {
            alert('Ошибка: ' + error.message);
        });
    });

    // Обработчик кнопок "Редактировать"
    document.querySelectorAll('.editBtn').forEach(function (button) {
        button.addEventListener('click', function () {
            if (isEditing) {
                alert('Сначала сохраните изменения текущей записи!');
                return;
            }

            let authorId = this.getAttribute('data-id');
            let row = document.getElementById('author_' + authorId);
            const saveButton = row.querySelector('.saveBtn');
            const editButton = this;

            row.querySelectorAll('.editable').forEach(function (cell) {
                let value = cell.textContent.trim();
                cell.innerHTML = `<input type="text" value="${value}" class="edit-input" data-column="${cell.getAttribute('data-column')}">`;
            });

            editButton.style.display = 'none';
            saveButton.style.display = 'inline-block';
            isEditing = true; // Устанавливаем состояние редактирования
        });
    });

    // Обработчик для кнопки "Сохранить изменения"
    document.querySelectorAll('.saveBtn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const authorId = this.dataset.id;
            const editButton = row.querySelector('.editBtn');
            const saveButton = this;

            // Собираем обновленные данные
            const updatedData = { author_id: authorId };
            let hasChanges = false; // Проверка, были ли изменения

            row.querySelectorAll('.edit-input').forEach(input => {
                const column = input.dataset.column;
                const newValue = input.value.trim();
                const originalValue = input.closest('.editable').getAttribute('data-original-value');

                if (newValue !== originalValue) {
                    updatedData[column] = newValue;
                    hasChanges = true;
                }
            });

            if (!hasChanges) {
                alert('Изменения не были внесены.');
                return;
            }

            // Отправляем данные на сервер
            fetch('update2.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updatedData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Данные успешно обновлены.');

                    // Обновляем содержимое ячеек
                    row.querySelectorAll('.editable').forEach(cell => {
                        const column = cell.getAttribute('data-column');
                        cell.textContent = updatedData[column] || cell.querySelector('input').value;
                        cell.removeAttribute('data-original-value'); // Удаляем оригинальное значение
                    });

                    // Возвращаем кнопки в исходное состояние
                    saveButton.style.display = 'none';
                    editButton.style.display = 'inline-block';

                    // Сбрасываем состояние редактирования
                    isEditing = false;
                } else {
                    alert('Ошибка обновления: ' + data.error);
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error.message);
            });
        });
    });

    // Обработчик кнопок "Удалить"
    document.querySelectorAll('.deleteBtn').forEach(function (button) {
        button.addEventListener('click', function () {
            let authorId = this.getAttribute('data-id');
            if (confirm('Вы уверены, что хотите удалить этого автора?')) {
                fetch('delete2.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ author_id: authorId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Автор удален!');
                        document.getElementById('author_' + authorId).remove();
                    } else {
                        alert('Ошибка при удалении автора: ' + data.error);
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