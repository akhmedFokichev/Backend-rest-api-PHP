#!/bin/bash

# Тестирование API через curl
# Запуск: bash test-api-cli.sh

BASE_URL="https://tradeapp.xsdk.ru"
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🧪 Тестирование Backend REST API"
echo "База: $BASE_URL"
echo ""

# 1. Проверка доступности
echo "1. Проверка доступности"
echo "GET /"
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')
if [ "$http_code" = "200" ]; then
    echo -e "${GREEN}✅ HTTP $http_code${NC}"
    echo "Ответ: $body"
else
    echo -e "${RED}❌ HTTP $http_code${NC}"
fi
echo ""

# 2. Тест регистрации
echo "2. Identity: Регистрация"
echo "POST /identity/registration"
TEST_EMAIL="test_$(date +%s)@example.com"
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/identity/registration" \
    -H "Content-Type: application/json" \
    -d "{\"login\":\"$TEST_EMAIL\",\"password\":\"Test123456!\"}")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')
if [ "$http_code" = "201" ] || [ "$http_code" = "409" ]; then
    echo -e "${GREEN}✅ HTTP $http_code${NC}"
    echo "Ответ: $body"
else
    echo -e "${RED}❌ HTTP $http_code${NC}"
    echo "Ответ: $body"
fi
echo ""

# 3. Тест логина
echo "3. Identity: Вход"
echo "POST /identity/login"
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/identity/login" \
    -H "Content-Type: application/json" \
    -d "{\"login\":\"$TEST_EMAIL\",\"password\":\"Test123456!\"}")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')
if [ "$http_code" = "200" ]; then
    echo -e "${GREEN}✅ HTTP $http_code${NC}"
    echo "Ответ: $body"
    # Извлекаем токены
    ACCESS_TOKEN=$(echo "$body" | grep -o '"accessToken":"[^"]*' | cut -d'"' -f4)
    REFRESH_TOKEN=$(echo "$body" | grep -o '"refreshToken":"[^"]*' | cut -d'"' -f4)
else
    echo -e "${RED}❌ HTTP $http_code${NC}"
    echo "Ответ: $body"
fi
echo ""

# 4. Тест обновления токена
if [ -n "$REFRESH_TOKEN" ]; then
    echo "4. Identity: Обновление токена"
    echo "POST /identity/refresh"
    response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/identity/refresh" \
        -H "Content-Type: application/json" \
        -d "{\"login\":\"$TEST_EMAIL\",\"refreshToken\":\"$REFRESH_TOKEN\"}")
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    if [ "$http_code" = "200" ]; then
        echo -e "${GREEN}✅ HTTP $http_code${NC}"
        echo "Токен обновлен"
    else
        echo -e "${RED}❌ HTTP $http_code${NC}"
    fi
    echo ""
fi

# 5. Тест получения списка стран
echo "5. Reference: Получение списка стран"
echo "GET /reference/country"
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/reference/country")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')
if [ "$http_code" = "200" ]; then
    echo -e "${GREEN}✅ HTTP $http_code${NC}"
    count=$(echo "$body" | grep -o '\[' | wc -l)
    echo "Получено записей: $count"
else
    echo -e "${RED}❌ HTTP $http_code${NC}"
fi
echo ""

# 6. Тест создания страны
echo "6. Reference: Создание страны"
echo "POST /reference/country"
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/reference/country" \
    -H "Content-Type: application/json" \
    -d "{\"code\":\"TEST\",\"name\":\"Тестовая страна $(date +%s)\",\"is_catalog\":false,\"sort_order\":0}")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')
if [ "$http_code" = "201" ]; then
    echo -e "${GREEN}✅ HTTP $http_code${NC}"
    echo "Ответ: $body"
    COUNTRY_UUID=$(echo "$body" | grep -o '"uuid":"[^"]*' | cut -d'"' -f4)
    echo "UUID: $COUNTRY_UUID"
else
    echo -e "${RED}❌ HTTP $http_code${NC}"
    echo "Ответ: $body"
fi
echo ""

# 7. Тест обновления страны
if [ -n "$COUNTRY_UUID" ]; then
    echo "7. Reference: Обновление страны"
    echo "PUT /reference/country/$COUNTRY_UUID"
    response=$(curl -s -w "\n%{http_code}" -X PUT "$BASE_URL/reference/country/$COUNTRY_UUID" \
        -H "Content-Type: application/json" \
        -d "{\"name\":\"Обновленная страна $(date +%s)\"}")
    http_code=$(echo "$response" | tail -n1)
    if [ "$http_code" = "204" ]; then
        echo -e "${GREEN}✅ HTTP $http_code - Страна обновлена${NC}"
    else
        echo -e "${RED}❌ HTTP $http_code${NC}"
    fi
    echo ""

    # 8. Тест удаления страны
    echo "8. Reference: Удаление страны"
    echo "DELETE /reference/country/$COUNTRY_UUID"
    response=$(curl -s -w "\n%{http_code}" -X DELETE "$BASE_URL/reference/country/$COUNTRY_UUID")
    http_code=$(echo "$response" | tail -n1)
    if [ "$http_code" = "204" ]; then
        echo -e "${GREEN}✅ HTTP $http_code - Страна удалена${NC}"
    else
        echo -e "${RED}❌ HTTP $http_code${NC}"
    fi
    echo ""
fi

# 9. Проверка OpenAPI документации
echo "9. Проверка OpenAPI документации"
echo "GET /api-docs.json"
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/api-docs.json")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')
if [ "$http_code" = "200" ]; then
    echo -e "${GREEN}✅ HTTP $http_code${NC}"
    if echo "$body" | grep -q '"openapi"'; then
        echo "✅ Валидная спецификация OpenAPI"
    fi
else
    echo -e "${RED}❌ HTTP $http_code${NC}"
fi
echo ""

echo "✅ Тестирование завершено"
echo "Swagger UI: $BASE_URL/swagger-ui.html"
