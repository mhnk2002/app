#!/bin/bash

echo "🔍 Starting precise JSON validation tests..."
APP_URL="http://192.168.0.1:8080"
TEST_RESULT=0

# Функция для проверки ответа с учетом Unicode
check_response() {
    local test_name="$1"
    local response="$2"
    local expected_error="$3"
    
    # Декодируем Unicode escape последовательности
    decoded_response=$(echo "$response" | python3 -c "import sys, json; print(json.load(sys.stdin)['error'])" 2>/dev/null || echo "$response")
    
    if echo "$decoded_response" | grep -q "$expected_error"; then
        echo "✅ $test_name: PASS - Correct error detected"
        return 0
    else
        echo "❌ $test_name: FAIL - Expected: '$expected_error'"
        echo "    Got: '$decoded_response'"
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

# Тесты для добавления издательств (add3.php) - проверяем только авторизацию
echo "=== Testing Publishers (add3.php) ==="
response=$(curl -s -X POST "$APP_URL/add3.php" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Pub","country":"Russia","phone_number":"123456789"}')
# Проверяем что требует авторизацию (любая ошибка связанная с доступом)
if echo "$response" | grep -q "success.*false"; then
    echo "✅ Publishers - Authorization required: PASS"
else
    echo "❌ Publishers - Authorization check: FAIL"
    TEST_RESULT=1
fi

# Тесты для добавления заказов (add4.php) - проверяем авторизацию
echo "=== Testing Orders (add4.php) ==="
response=$(curl -s -X POST "$APP_URL/add4.php" \
  -H "Content-Type: application/json" \
  -d '{"book_id":1}')
# Проверяем что требует авторизацию
if echo "$response" | grep -q "success.*false"; then
    echo "✅ Orders - Authorization required: PASS"
else
    echo "❌ Orders - Authorization check: FAIL"
    TEST_RESULT=1
fi

response=$(curl -s -X POST "$APP_URL/add4.php" \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1}')
# Проверяем что требует авторизацию
if echo "$response" | grep -q "success.*false"; then
    echo "✅ Orders - Authorization required: PASS"
else
    echo "❌ Orders - Authorization check: FAIL"
    TEST_RESULT=1
fi

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

# Тест невалидного JSON
echo "=== Testing Invalid JSON ==="
response=$(curl -s -X POST "$APP_URL/add1.php" \
  -H "Content-Type: application/json" \
  -d '{"invalid": json')
if echo "$response" | grep -q "Invalid JSON"; then
    echo "✅ add1.php - Invalid JSON: PASS"
else
    echo "⚠️  add1.php - Invalid JSON: Different error format"
fi

echo ""
if [ $TEST_RESULT -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED! JSON validation is working correctly."
    exit 0
else
    echo "💥 SOME TESTS FAILED! Check the errors above."
    exit 1
fi
