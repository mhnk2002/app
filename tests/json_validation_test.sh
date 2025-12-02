#!/bin/bash

echo "Starting JSON validation tests..."
APP_URL="http://192.168.0.1:8080"
TEST_RESULT=0

# -----------------------------
# LOGIN AND OBTAIN REAL COOKIE
# -----------------------------
echo "Logging into system to obtain PHPSESSID..."

LOGIN_RESPONSE=$(curl -i -s -X POST "$APP_URL/login.php" \
     -d "username=admin&password=1")

PHPSESSID=$(echo "$LOGIN_RESPONSE" | grep -oP 'PHPSESSID=\w+')

if [ -z "$PHPSESSID" ]; then
    echo "❌ FAILED: Cannot get session cookie (PHPSESSID)"
    exit 1
fi

echo "➡ Obtained session cookie: $PHPSESSID"

# ------------------------------------
# FUNCTION TO TEST JSON ENDPOINTS WITHOUT jq
# ------------------------------------
test_case() {
    local name="$1"
    local endpoint="$2"
    local json="$3"
    local expected="$4"
    local require_auth="$5"

    if [ "$require_auth" = "yes" ]; then
        cookies=(-H "Cookie:$PHPSESSID")
    else
        cookies=()
    fi

    response=$(curl -s -X POST "$APP_URL/$endpoint" \
        -H "Content-Type: application/json" \
        "${cookies[@]}" \
        -d "$json")

    # Try to extract success and error fields using grep/sed
    success=$(echo "$response" | grep -o '"success":[^,}]*' | cut -d':' -f2 | tr -d ' ')
    error=$(echo "$response" | grep -o '"error":"[^"]*"' | cut -d':' -f2- | sed 's/^"//;s/"$//')

    # Determine pass/fail
    if [[ "$expected" == "success:true" && "$success" == "true" ]]; then
        echo "✅ $name: PASS"
    elif [[ "$error" == *"$expected"* ]]; then
        echo "✅ $name: PASS"
    else
        echo "❌ $name: FAIL"
        echo "   EXPECTED: $expected"
        echo "   GOT: $response"
        TEST_RESULT=1
    fi
}

echo "=== Testing add2.php validation ==="

# Missing name
test_case "add2 - Missing name" "add2.php" \
'{"surname":"Test","country":"Russia","date_of_birth":"1990-01-01"}' \
"Missing required field: name"

# Missing surname
test_case "add2 - Missing surname" "add2.php" \
'{"name":"Test","country":"Russia","date_of_birth":"1990-01-01"}' \
"Missing required field: surname"

# No JSON sent
test_case "add2 - No JSON" "add2.php" \
"" \
"No JSON received"


# Invalid JSON
test_case "add2 - Invalid JSON" "add2.php" \
'{"name":"Test",' \
"Invalid JSON"

# Name contains digits → should fail
test_case "add2 - Digits in name" "add2.php" \
'{"name":"T3st","surname":"Stolyarov","country":"Russia","date_of_birth":"2000-01-01"}' \
"Invalid characters in name"

# Valid request
test_case "add2 - Valid request" "add2.php" \
'{"name":"Bogden","surname":"Stolyarov","country":"Russia","date_of_birth":"2004-01-01"}' \
"success:true"

echo "=== Testing add3.php validation ==="

# Country contains digits → must fail
test_case "add3 - Country contains digits" "add3.php" \
'{"name":"PublisherX","country":"Rus5sia","phone_number":"12345678"}' \
"Invalid characters in country" yes

# Phone number too short
test_case "add3 - Phone too short" "add3.php" \
'{"name":"PublisherY","country":"Россия","phone_number":"123"}' \
"Invalid phone number format" yes

# Phone number contains letters
test_case "add3 - Phone contains letters" "add3.php" \
'{"name":"PublisherZ","country":"Россия","phone_number":"12ab3456"}' \
"Invalid phone number format" yes

# Valid request with unique phone
UNIQUE_PHONE=$((1000000000 + RANDOM % 9000000000))
test_case "add3 - Valid request" "add3.php" \
"{\"name\":\"AST$RANDOM\",\"country\":\"Россия\",\"phone_number\":\"$UNIQUE_PHONE\"}" \
"success:true" yes

echo ""
if [ $TEST_RESULT -eq 0 ]; then
    echo "ALL TESTS PASSED"
    exit 0
else
    echo "TESTS FAILED"
    exit 1
fi
