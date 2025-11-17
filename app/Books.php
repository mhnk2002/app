<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Книги</title>
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
            max-width: 1400px;
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

        #addBookForm {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			z-index: 1000;
			background-color: white;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
			padding: 20px;
			border-radius: 10px;
			display: none;
			max-width: 500px;
			width: 90%;
			max-height: 90%;
			overflow-y: auto; /* Прокрутка, если форма не помещается */
		}

        #overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			z-index: 999;
			display: none;
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
<body class="books-page">
<div id="overlay"></div> <!-- Затемнение фона -->

<div class="container">
    <button class="home-button">
        <a href="index.php" title="На главную">&#8962;</a>
    </button>
    <h1>Книги</h1>
    <table>
        <thead>
        <tr>
            <th>Название</th>
            <th>Автор</th>
            <th>Год издания</th>
            <th>Жанр</th>
            <th>Цена</th>
			<th>Издательство</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include("dbook.php");


		// Загружаем список авторов
		$authorsQuery = "SELECT author_id, name, surname FROM Authors";
		$authorsResult = mysqli_query($dbook, $authorsQuery);
		if (!$authorsResult) {
			die("Ошибка получения списка авторов: " . mysqli_error($dbook));
		}
		
        $query = "SELECT 
                    b.book_id, 
                    b.title, 
                    a.name AS author_name, 
                    a.surname AS author_surname, 
                    b.god_izdaniya, 
                    b.genre, 
                    b.price,
					p.name AS publisher_name
                  FROM Books b
                  JOIN Authors a ON b.author_id = a.author_id
				  JOIN Publishers p ON b.publisher_id = p. publisher_id";
        $result = mysqli_query($dbook, $query);

        if (!$result) {
            echo "<tr><td colspan='6'>Ошибка при выполнении запроса: " . mysqli_error($dbook) . "</td></tr>";
        } else {
            while ($book = mysqli_fetch_assoc($result)) {
                echo "<tr id='book_{$book['book_id']}'>";
                echo "<td class='editable' data-column='title'>{$book['title']}</td>";
                echo "<td>{$book['author_name']} {$book['author_surname']}</td>";
                echo "<td class='editable' data-column='god_izdaniya'>{$book['god_izdaniya']}</td>";
                echo "<td class='editable' data-column='genre'>{$book['genre']}</td>";
                echo "<td class='editable' data-column='price'>{$book['price']}</td>";
				echo "<td>{$book['publisher_name']}</td>";
                echo "<td>
                        <button class='editBtn button' data-id='{$book['book_id']}'>Редактировать</button>
                        <button class='saveBtn button' data-id='{$book['book_id']}' style='display: none;'>Сохранить изменения</button>
                        <button class='deleteBtn button' data-id='{$book['book_id']}'>Удалить</button>
                      </td>";
                echo "</tr>";
            }
        }
        ?>
        </tbody>
    </table>

    <!-- Кнопка добавления книги -->
    <button id="addBookBtn" class="button">Добавить новую книгу</button>

    <!-- Форма добавления книги -->
    <div id="addBookForm" class="form-container">
        <h2>Добавить новую книгу</h2>
        <form id="addBook">
            <div class="form-group">
                <label for="title">Название книги:</label>
                <input type="text" id="title" name="title" class="input-field" required>
            </div>
            <div class="form-group">
                <label for="authorSelect">Автор:</label>
                <select id="authorSelect" name="author_id" class="select-field" required>
                    <option value="" disabled selected>Выберите автора</option>
                    <?php while ($author = mysqli_fetch_assoc($authorsResult)) { ?>
                        <option value="<?=$author['author_id']?>">
                            <?=$author['name']?> <?=$author['surname']?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="publisher_id">Издательство:</label>
                <select id="publisher_id" name="publisher_id" class="select-field" required>
                    <option value="" disabled selected>Выберите издательство</option>
                    <?php
                    $publishersQuery = "SELECT publisher_id, name FROM Publishers";
                    $publishersResult = mysqli_query($dbook, $publishersQuery);
                    while ($publisher = mysqli_fetch_assoc($publishersResult)) {
                        echo "<option value='{$publisher['publisher_id']}'>{$publisher['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="god_izdaniya">Год издания:</label>
                <input type="number" id="god_izdaniya" name="god_izdaniya" class="input-field" required>
            </div>
            <div class="form-group">
                <label for="genre">Жанр:</label>
                <input type="text" id="genre" name="genre" class="input-field" required>
            </div>
            <div class="form-group">
                <label for="price">Цена:</label>
                <input type="number" id="price" name="price" step="1" class="input-field" required>
            </div>
            <div class="form-group">
            <button type="submit" class="button submit-button">Добавить книгу</button>
        </form>
    </div>
</div>

<script>
// Переменная для отслеживания состояния редактирования
let isEditing = false;

// Обработчик для кнопки "Редактировать"
document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function () {
        if (isEditing) {
            alert('Сначала сохраните изменения текущей строки!');
            return;
        }

        const row = this.closest('tr');
        const saveButton = row.querySelector('.saveBtn');
        const editButton = this;

        // Делаем ячейки редактируемыми
        row.querySelectorAll('.editable').forEach(cell => {
            const column = cell.dataset.column;
            const value = cell.textContent.trim();
            cell.innerHTML = `<input type="text" data-column="${column}" value="${value}" class="edit-input">`;
        });

        // Показываем кнопку "Сохранить изменения"
        editButton.style.display = 'none';
        saveButton.style.display = 'inline-block';

        // Устанавливаем состояние редактирования
        isEditing = true;
    });
});

// Обработчик для кнопки "Сохранить изменения"
document.querySelectorAll('.saveBtn').forEach(button => {
    button.addEventListener('click', function () {
        const row = this.closest('tr');
        const bookId = this.dataset.id;
        const editButton = row.querySelector('.editBtn');
        const saveButton = this;

        // Собираем обновленные данные
        const updatedData = { book_id: bookId };
        row.querySelectorAll('.edit-input').forEach(input => {
            const column = input.dataset.column;
            updatedData[column] = input.value.trim();
        });

        // Отправляем данные на сервер
        fetch('update1.php', {
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
                    const column = cell.dataset.column;
                    cell.textContent = updatedData[column];
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

// Обработчик для кнопки "Удалить"
document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function () {
        const bookId = this.dataset.id;

        if (confirm('Вы уверены, что хотите удалить эту книгу?')) {
            fetch('delete1.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ book_id: bookId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Книга успешно удалена.');
                    // Удаляем строку из таблицы
                    document.getElementById(`book_${bookId}`).remove();
                } else {
                    alert('Ошибка удаления: ' + data.error);
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error.message);
            });
        }
    });
});

// Открытие формы добавления книги
document.getElementById('addBookBtn').addEventListener('click', function () {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('addBookForm').style.display = 'block';
});

// Закрытие формы добавления книги при клике на затемнение
document.getElementById('overlay').addEventListener('click', function () {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('addBookForm').style.display = 'none';
});

// Отправка данных формы на сервер
document.getElementById('addBook').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const bookData = Object.fromEntries(formData.entries());

    fetch('add1.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Книга успешно добавлена!');
            location.reload();
        } else {
            alert('Ошибка: ' + data.error);
        }
    })
    .catch(error => alert('Ошибка: ' + error.message));
});
</script>
</body>
</html>
