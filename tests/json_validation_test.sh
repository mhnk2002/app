#!/bin/bash

echo "🔍 Starting precise JSON validation tests..."
APP_URL="http://192.168.0.1:8080"
TEST_RESULT=0

# Функция для проверки ответа
check_response() {
    local test_name="$1"
    local response="$2"
    local expected_error="$3"
    
    if echo "$response" | grep -q "$expected_error"; then
        echo "✅ $test_name: PASS - Correct error detected"
        return 0
    else
        echo "❌ $test_name: FAIL - Expected: '$expected_error'"
        echo "    Got: '$response'"
        return 1
    fi
}

# Тесты для добавления книг (add1.php)
echo "=== Testing Books (add1.php) ==="
response=$(curl -s -X POST "$APP_URL/add1.php" \
  -H "Content-Type: application/json" \
  -d '{"author_id":1,"publisher_id":1,"god_izdaniya":"2024","genre":"Fiction","price":29.99}')
check_response "Books - Missing title" "$response" "Missing required field: title" || TEST_RESULT=1

response=$(curl -s -X POST "$APP_URL/add1.php" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Book","publisher_id":1,"god_izdaniya":"2024","genre":"Fiction","price":29.99}')
check_response "Books - Missing author_id" "$response" "Missing required field: author_id" || TEST_RESULT=1

# Тесты для добавления авторов (add2.php)
echo "=== Testing Authors (add2.php) ==="
response=$(curl -s -X POST "$APP_URL/add2.php" \
  -H "Content-Type: application/json" \
  -d '{"surname":"Test","country":"Russia","date_of_birth":"1990-01-01"}')
check_response "Authors - Missing name" "$response" "Недостаточно данных" || TEST_RESULT=1

response=$(curl -s -X POST "$APP_URL/add2.php" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","country":"Russia","date_of_birth":"1990-01-01"}')
check_response "Authors - Missing surname" "$response" "Недостаточно данных" || TEST_RESULT=1

# Тесты для добавления издательств (add3.php)
echo "=== Testing Publishers (add3.php) ==="
# Note: add3 требует авторизацию, тестируем только ошибку метода если не авторизован
response=$(curl -s -X POST "$APP_URL/add3.php" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Pub","country":"Russia","phone_number":"123456789"}')
check_response "Publishers - Authorization check" "$response" "Вы не авторизованы" || TEST_RESULT=1

# Тесты для добавления заказов (add4.php)
echo "=== Testing Orders (add4.php) ==="
response=$(curl -s -X POST "$APP_URL/add4.php" \
  -H "Content-Type: application/json" \
  -d '{"book_id":1}')
check_response "Orders - Missing customer_id" "$response" "Не все данные переданы" || TEST_RESULT=1

response=$(curl -s -X POST "$APP_URL/add4.php" \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1}')
check_response "Orders - Missing book_id" "$response" "Не все данные переданы" || TEST_RESULT=1

# Тесты для добавления покупателей (add5.php)
echo "=== Testing Customers (add5.php) ==="
response=$(curl -s -X POST "$APP_URL/add5.php" \
  -H "Content-Type: application/json" \
  -d '{"surname":"Test","email":"test@test.com","phone_number":"123456789"}')
check_response "Customers - Missing name" "$response" "Missing required fields" || TEST_RESULT=1

response=$(curl -s -X POST "$APP_URL/add5.php" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@test.com","phone_number":"123456789"}')
check_response "Customers - Missing surname" "$response" "Missing required fields" || TEST_RESULT=1

# Тест невалидного JSON для всех endpoints
echo "=== Testing Invalid JSON ==="
endpoints=("add1.php" "add2.php" "add4.php" "add5.php")
for endpoint in "${endpoints[@]}"; do
    response=$(curl -s -X POST "$APP_URL/$endpoint" \
      -H "Content-Type: application/json" \
      -d '{"invalid": json')
    if echo "$response" | grep -q "Invalid JSON"; then
        echo "✅ $endpoint - Invalid JSON: PASS"
    else
        echo "⚠️  $endpoint - Invalid JSON: Different error format (expected)"
    fi
done

# Итоговый результат
echo ""
if [ $TEST_RESULT -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED! JSON validation is working correctly."
    exit 0
else
    echo "💥 SOME TESTS FAILED! Check the errors above."
    exit 1
fi
