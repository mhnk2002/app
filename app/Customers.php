<?php  
session_start(); 
 
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { 
    header('Location: login.php'); 
    exit(); 
} 
 
include("dbook.php"); 
 
// Получение списка покупателей с полями address и phone_number 
$customersQuery = "SELECT customer_id, name, surname, email, address, phone_number FROM Customers"; 
$customersResult = mysqli_query($dbook, $customersQuery); 
 
// Получение активных покупателей 
$activeCustomersQuery = "SELECT name, surname, email FROM Customers WHERE customer_id IN (SELECT customer_id FROM customers2purchases)"; 
$activeCustomersResult = mysqli_query($dbook, $activeCustomersQuery); 
 
// Суммарная стоимость книг 
$sumQuery = "SELECT name, surname, SUM(price) AS total_cost  
             FROM Customers  
             JOIN Orders ON Customers.customer_id = Orders.customer_id  
             JOIN OrdersBooks ON Orders.order_id = OrdersBooks.order_id  
             JOIN Books ON OrdersBooks.book_id = Books.book_id  
             GROUP BY Customers.customer_id"; 
$sumResult = mysqli_query($dbook, $sumQuery); 
?>
<!DOCTYPE html> 
<html lang="ru"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Customers</title> 
    <link rel="stylesheet" href="styles.css">
	<style>
        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
			max-height: 100vh;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 8px;
        }
		
		.modal-content form {
            display: flex;
			flex-direction: column;
			gap: 0px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>
</head> 
<body class="customers-page"> 
    <div class="container"> 
        <!-- Кнопка-домик --> 
        <button class="home-button"> 
            <a href="index.php" title="На главную">&#8962;</a> 
        </button> 
 
        <h1>Покупатели</h1> 
	<div class="table-container">
        <h2>Список покупателей</h2> 
        <table> 
            <thead> 
                <tr> 
                    <th>Имя</th> 
                    <th>Фамилия</th> 
                    <th>Email</th> 
                    <th>Адрес</th> 
                    <th>Телефон</th> 
                    <th>Действия</th> 
                </tr> 
            </thead> 
            <tbody>
    <?php while ($customer = mysqli_fetch_assoc($customersResult)) : ?>
        <tr id="customer_<?= $customer['customer_id'] ?>">
    <td class="editable" data-column="name"><?= htmlspecialchars($customer['name']) ?></td>
    <td class="editable" data-column="surname"><?= htmlspecialchars($customer['surname']) ?></td>
    <td class="editable" data-column="email"><?= htmlspecialchars($customer['email']) ?></td>
    <td id="address-<?= $customer['customer_id'] ?>" class="editable hidden-section"><?= htmlspecialchars($customer['address']) ?></td>
    <td id="phone_number-<?= $customer['customer_id'] ?>" class="editable hidden-section"><?= htmlspecialchars($customer['phone_number']) ?></td>
    <td>
        <button class="toggleDetails" data-id="<?= $customer['customer_id'] ?>">Показать личные данные</button>
        <button class="editBtn" data-id="<?= $customer['customer_id'] ?>">Редактировать</button>
        <button class="deleteBtn" data-id="<?= $customer['customer_id'] ?>">Удалить</button>
    </td>
</tr>

    <?php endwhile; ?>
			</tbody>

        </table> 
    </div>     
 
        <div id="addCustomerModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Добавление нового покупателя</h2>
                <form id="addCustomer" action="add5.php" method="POST">
                    <label for="name">Имя:</label>
                    <input type="text" name="name" required><br><br>
                    <label for="surname">Фамилия:</label>
                    <input type="text" name="surname" required><br><br>
                    <label for="email">Email:</label>
                    <input type="email" name="email" required><br><br>
                    <label for="address">Адрес:</label>
                    <input type="text" name="address"><br><br>
                    <label for="phone_number">Телефон:</label>
                    <input type="text" name="phone_number"><br><br>
                    <button type="submit" class="button">Добавить</button>
                </form>
            </div>
        </div>
	<div class="button-conatiner">
		<button class="button" onclick="openModal()">Добавить нового покупателя</button> 
        <button class="button" data-section="activeCustomers">Активные покупатели</button> 
		<button class="button" data-section="sumSection">Суммарная стоимость книг</button> 
	</div>
	
        <div id="activeCustomers" class="hidden-section1"> 
            <h2>Активные покупатели</h2> 
            <table> 
                <thead> 
                    <tr> 
                        <th>Имя</th> 
                        <th>Фамилия</th> 
                        <th>Email</th> 
                    </tr> 
                </thead> 
                <tbody> 
                    <?php while ($active = mysqli_fetch_assoc($activeCustomersResult)) : ?> 
                        <tr> 
                            <td><?= htmlspecialchars($active['name']) ?></td> 
                            <td><?= htmlspecialchars($active['surname']) ?></td> 
                            <td><?= htmlspecialchars($active['email']) ?></td> 
                        </tr> 
                    <?php endwhile; ?> 
                </tbody> 
            </table> 
        </div> 
 
        <div id="sumSection" class="hidden-section1"> 
            <h2>Суммарная стоимость книг</h2> 
            <table> 
                <thead> 
                    <tr> 
                        <th>Имя</th> 
                        <th>Фамилия</th> 
                        <th>Сумма</th> 
                        <th>Скидка</th> 
                    </tr> 
                </thead> 
                <tbody> 
                    <?php while ($sum = mysqli_fetch_assoc($sumResult)) : ?> 
                        <tr> 
                            <td><?= htmlspecialchars($sum['name']) ?></td> 
                            <td><?= htmlspecialchars($sum['surname']) ?></td> 
                            <td><?= htmlspecialchars($sum['total_cost']) ?></td> 
                            <td><?= $sum['total_cost'] > 1000 ? $sum['total_cost'] * 0.1 : 0 ?></td> 
                        </tr> 
                    <?php endwhile; ?> 
                </tbody> 
            </table> 
        </div> 
    </div> 
 
    <script>
		let isEditing = false;
		
        function toggleSection(sectionId) { 
            const section = document.getElementById(sectionId); 
			if (section) {
            section.style.display = section.style.display === "block" ? "none" : "block"; 
			} 
		}
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.button[data-section]').forEach(function (button) {
				button.addEventListener('click', function () {
					const sectionId = this.getAttribute('data-section');
					toggleSection(sectionId);
				});
			});
		});
 
 // Обработчик формы добавления заказа
    const addCustomer = document.getElementById('addCustomer');
    addCustomer.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('add5.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(formData.entries())),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Покупатель успешно добавлен!');
                    location.reload();
                } else {
                    alert('Ошибка при добавлении покупателя: ' + data.error);
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error.message);
            });
    });
 
 
        function showAddForm() { 
            document.getElementById('addCustomerForm').style.display = "block"; 
        } 
 
        function toggleDetails(customerId) {
    const addressCell = document.getElementById('address-' + customerId);
    const phoneCell = document.getElementById('phone_number-' + customerId);

    if (addressCell && phoneCell) { // Проверяем, что элементы существуют
        if (addressCell.classList.contains('hidden-section')) {
            addressCell.classList.remove('hidden-section');
            phoneCell.classList.remove('hidden-section');
        } else {
            addressCell.classList.add('hidden-section');
            phoneCell.classList.add('hidden-section');
        }
    } else {
        console.error('Элементы addressCell или phoneCell не найдены для customerId:', customerId);
    }
}
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.toggleDetails').forEach(function (button) {
				button.addEventListener('click', function () {
					const customerId = this.getAttribute('data-id');
					toggleDetails(customerId);
				});
			});
		});
 
        document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.editBtn').forEach(function (button) {
    button.addEventListener('click', function () {
        let customerId = this.getAttribute('data-id'); // ID клиента
        let row = document.getElementById('customer_' + customerId);

		if  (isEditing && !this.classList.contains('editing')) {
			alert('Сначала завершите редактирование текущей строки.');
			return;
		}

        if (this.classList.contains('editing')) {
            let updatedData = {};
            let hasError = false;

            // Обработка всех редактируемых ячеек
            row.querySelectorAll('.editable').forEach(function (cell) {
                let column;
                let value;

                // Если у ячейки есть data-column, используем его
                if (cell.hasAttribute('data-column')) {
                    column = cell.getAttribute('data-column');
                    value = cell.querySelector('input').value; // Получаем значение из input
                } 
                // Если у ячейки есть id, разбиваем его
                else if (cell.id) {
                    let idParts = cell.id.split('-'); // Разделяем id по "-"
                    if (idParts.length < 2) {
                        alert('Ошибка: неправильный формат ID ' + cell.id);
                        hasError = true;
                        return;
                    }
                    column = idParts[0]; // Берем первую часть как имя столбца
                    value = cell.querySelector('input').value;
                } else {
                    alert('Ошибка: ячейка не содержит информации о столбце.');
                    hasError = true;
                    return;
                }

                updatedData[column] = value;
            });

            if (hasError) return;

            // Отправляем данные на сервер
            fetch('save_customer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customer_id: customerId,
                    ...updatedData
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Данные успешно обновлены!');
                        location.reload(); // Перезагрузка страницы
                    } else {
                        alert('Ошибка при обновлении данных: ' + data.error);
                    }
                })
                .catch(error => alert('Ошибка: ' + error.message));

            this.classList.remove('editing');
            this.textContent = 'Редактировать';
			isEditing = false;
        } else {
            // Преобразуем содержимое ячеек в input
            row.querySelectorAll('.editable').forEach(function (cell) {
                let value = cell.textContent.trim();
                cell.innerHTML = `<input type="text" value="${value}">`;
            });

            this.textContent = 'Сохранить';
            this.classList.add('editing');
			isEditing = true;
        }
    });
});


    // Обработчик кнопок "Удалить"
    document.querySelectorAll('.deleteBtn').forEach(function (button) {
        button.addEventListener('click', function () {
            let customerId = this.getAttribute('data-id');
            if (confirm('Вы уверены, что хотите удалить этого покупателя?')) {
                fetch('delete5.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ customer_id: customerId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Покупатель удален!');
                        document.getElementById('customer_' + customerId).remove();
                    } else {
                        alert('Ошибка при удалении покупателя: ' + data.error);
                    }
                })
                .catch(error => alert('Ошибка: ' + error.message));
            }
        });
    });
});
		// Открытие модального окна
        function openModal() {
            document.getElementById('addCustomerModal').style.display = "block";
        }

        // Закрытие модального окна
        function closeModal() {
            document.getElementById('addCustomerModal').style.display = "none";
        }

        // Закрытие модального окна при клике вне контента
        window.onclick = function(event) {
            const modal = document.getElementById('addCustomerModal');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script> 
</body> 
</html>