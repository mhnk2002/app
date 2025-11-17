<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include("dbook.php"); // Подключаем базу данных

// Получение текущих заказов
$currentOrdersQuery = "
    SELECT Orders.order_id, Orders.customer_id, Orders.status, Orders.order_date, 
           Customers.name AS customer_name, Customers.surname AS customer_surname, 
           Books.title AS book_title
    FROM Orders
    JOIN Customers ON Orders.customer_id = Customers.customer_id
    JOIN Books ON Orders.book_id = Books.book_id
    WHERE Orders.status = 'В процессе'";
$currentOrdersResult = mysqli_query($dbook, $currentOrdersQuery);

if (!$currentOrdersResult) {
    die("Ошибка выполнения запроса текущих заказов: " . mysqli_error($dbook));
}

// Получение архивных заказов
$archiveOrdersQuery = "
    SELECT Orders.order_id, Orders.customer_id, Orders.status, Orders.order_date, 
           Customers.name AS customer_name, Customers.surname AS customer_surname, 
           Books.title AS book_title
    FROM Orders
    JOIN Customers ON Orders.customer_id = Customers.customer_id
    JOIN Books ON Orders.book_id = Books.book_id
    WHERE Orders.status = 'Готов'";
$archiveOrdersResult = mysqli_query($dbook, $archiveOrdersQuery);

if (!$archiveOrdersResult) {
    die("Ошибка выполнения запроса архивных заказов: " . mysqli_error($dbook));
}

// Загружаем список клиентов
$customersQuery = "SELECT customer_id, name, surname FROM Customers";
$customersResult = mysqli_query($dbook, $customersQuery);
if (!$customersResult) {
    die("Ошибка получения списка клиентов: " . mysqli_error($dbook));
}

// Загружаем список книг
$booksQuery = "SELECT book_id, title FROM Books";
$booksResult = mysqli_query($dbook, $booksQuery);
if (!$booksResult) {
    die("Ошибка получения списка книг: " . mysqli_error($dbook));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказы</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="orders-page">
    <div class="container">
        <button class="home-button">
            <a href="index.php" title="На главную">&#8962;</a>
        </button>
        <h1>Заказы</h1>
        
        <!-- Раздел текущих заказов -->
        <div class="table-container">
            <h2>Текущие заказы</h2>
            <table>
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Название книги</th>
                        <th>Статус</th>
                        <th>Дата заказа</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($currentOrdersResult)) { ?>
                    <tr id="order_<?=$order['order_id']?>" data-customer="<?=$order['customer_id']?>">
                        <td><?=$order['customer_name']?></td>
                        <td><?=$order['customer_surname']?></td>
                        <td><?=$order['book_title']?></td>
                        <td class="editable" data-column="status"><?=$order['status']?></td>
                        <td class="editable" data-column="order_date"><?=$order['order_date']?></td>
						<td>
							<button class="editBtn button" data-id="<?=$order['order_id']?>">Редактировать</button>
							<button class="deleteBtn button" data-id="<?=$order['order_id']?>">Удалить</button>
						</td>	
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
	<div class="button-container">
        <!-- Кнопка для отображения архива заказов -->
        <button class="button" id="toggleArchive">Показать архив заказов</button>
		<!-- Кнопка для добавления нового заказа -->
        <button id="addOrderButton" class="button">Добавить новый заказ</button>
	</div>
        <!-- Раздел архива заказов -->
        <div class="table-container hidden" id="archiveOrders">
            <h2>Архив заказов</h2>
            <table>
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Название книги</th>
                        <th>Статус</th>
                        <th>Дата заказа</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($archiveOrdersResult)) { ?>
                    <tr id="order_<?=$order['order_id']?>" data-customer="<?=$order['customer_id']?>">
                        <td><?=$order['customer_name']?></td>
                        <td><?=$order['customer_surname']?></td>
                        <td><?=$order['book_title']?></td>
                        <td class="editable" data-column="status"><?=$order['status']?></td>
                        <td class="editable" data-column="order_date"><?=$order['order_date']?></td>
						<td>
							<button class="editBtn button" data-id="<?=$order['order_id']?>">Редактировать</button>
							<button class="deleteBtn button" data-id="<?=$order['order_id']?>">Удалить</button>
						</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<!-- Модальное окно -->
    <div id="addOrderModal" class="modal hidden">
        <div class="modal-content">
            <h2>Добавить новый заказ</h2>
            <form id="addOrderForm">
                <label for="customerSelect">Покупатель:</label>
                <select id="customerSelect" name="customer_id" class="select-field" required>
                    <option value="" disabled selected>Выберите покупателя</option>
                    <?php while ($customer = mysqli_fetch_assoc($customersResult)) { ?>
                        <option value="<?=$customer['customer_id']?>">
                            <?=$customer['name']?> <?=$customer['surname']?>
                        </option>
                    <?php } ?>
                </select>
                
                <label for="bookSelect">Книга:</label>
                <select id="bookSelect" name="book_id" class="select-field" required>
                    <option value="" disabled selected>Выберите книгу</option>
                    <?php while ($book = mysqli_fetch_assoc($booksResult)) { ?>
                        <option value="<?=$book['book_id']?>"><?=$book['title']?></option>
                    <?php } ?>
                </select>

                <button type="submit">Сохранить</button>
                <button type="button" id="cancelButton">Отмена</button>
            </form>
        </div>
    </div>

<script>
// Скрипт для переключения отображения архива заказов
document.getElementById('toggleArchive').addEventListener('click', function () {
    const archiveSection = document.getElementById('archiveOrders');
    archiveSection.classList.toggle('hidden');
    const isHidden = archiveSection.classList.contains('hidden');
    this.textContent = isHidden ? 'Показать архив заказов' : 'Скрыть архив заказов';
});

document.addEventListener('DOMContentLoaded', () => {
    let editingRow = null; // Хранение редактируемой строки

    // Открытие модального окна для добавления заказа
    const modal = document.getElementById('addOrderModal');
    const openModalBtn = document.getElementById('addOrderButton');
    const cancelModalBtn = document.getElementById('cancelButton');

    openModalBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    cancelModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Обработчик формы добавления заказа
    const addOrderForm = document.getElementById('addOrderForm');
    addOrderForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('add4.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(formData.entries())),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Заказ успешно добавлен!');
                    location.reload();
                } else {
                    alert('Ошибка при добавлении заказа: ' + data.error);
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error.message);
            });
    });

    // Обработчик для кнопок "Редактировать"
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');

            // Проверка, редактируется ли уже другая строка
            if (editingRow && editingRow !== row) {
                alert('Сохраните изменения перед редактированием другой строки.');
                return;
            }

            editingRow = row; // Установить текущую строку как редактируемую
            const saveButton = document.createElement('button');
            saveButton.textContent = 'Сохранить изменения';
            saveButton.className = 'saveBtn button';

            this.style.display = 'none';
            this.after(saveButton);

            row.querySelectorAll('.editable').forEach(cell => {
                const column = cell.dataset.column;
                const value = cell.textContent.trim();
                cell.innerHTML = `<input type="text" data-column="${column}" value="${value}" class="edit-input">`;
            });

            saveButton.addEventListener('click', function () {
                const updatedData = { order_id: row.id.split('_')[1] };
                row.querySelectorAll('.edit-input').forEach(input => {
                    updatedData[input.dataset.column] = input.value.trim();
                });

                fetch('update4.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updatedData),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Данные успешно обновлены.');
                            row.querySelectorAll('.editable').forEach(cell => {
                                const column = cell.dataset.column;
                                cell.textContent = updatedData[column];
                            });
                            saveButton.remove();
                            button.style.display = 'inline-block';
                            editingRow = null; // Сброс редактируемой строки
                        } else {
                            alert('Ошибка обновления: ' + data.error);
                        }
                    })
                    .catch(error => {
                        alert('Ошибка: ' + error.message);
                    });
            });
        });
    });

    // Обработчик для кнопок "Удалить"
    document.querySelectorAll('.deleteBtn').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.dataset.id;

            if (confirm('Вы уверены, что хотите удалить этот заказ?')) {
                fetch('delete4.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Заказ успешно удален.');
                            document.getElementById(`order_${orderId}`).remove();
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
});
</script>
</body>
</html>
