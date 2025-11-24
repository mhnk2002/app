#!/bin/bash

echo "🔍 Starting JSON validation tests..."
APP_URL="http://192.168.0.1:8080"
TEST_RESULT=0

test_case() {
    local name="$1"
    local endpoint="$2"
    local json="$3"
    local expected="$4"

    response=$(curl -s -X POST "$APP_URL/$endpoint" \
        -H "Content-Type: application/json" \
        -d "$json")

    if echo "$response" | grep -q "$expected"; then
        echo "✅ $name: PASS"
    else
        echo "❌ $name: FAIL"
        echo "   EXPECTED: $expected"
        echo "   GOT: $response"
        TEST_RESULT=1
    fi
}

echo "=== Testing add2.php validation ==="

# Нет name
test_case "Missing name" "add2.php" \
'{"surname":"Test","country":"Russia","date_of_birth":"1990-01-01"}' \
"Missing required field: name"

# Нет surname
test_case "Missing surname" "add2.php" \
'{"name":"Test","country":"Russia","date_of_birth":"1990-01-01"}' \
"Missing required field: surname"

# Невалидный JSON
test_case "Invalid JSON" "add2.php" \
'{"invalid": json}' \
"Invalid JSON"

# Корректный JSON
test_case "Valid request" "add2.php" \
'{"name":"A","surname":"B","country":"C","date_of_birth":"2000-01-01"}' \
'"success":true'

echo ""
if [ $TEST_RESULT -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED"
    exit 0
else
    echo "💥 TESTS FAILED"
    exit 1
fi
