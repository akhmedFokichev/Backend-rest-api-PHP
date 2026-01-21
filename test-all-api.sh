#!/bin/bash

# Полное тестирование API
# Использование: ./test-all-api.sh

BASE_URL="${1:-https://tradeapp.xsdk.ru}"

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "╔════════════════════════════════════════════════════════╗"
echo "║         Тестирование Backend REST API                 ║"
echo "║         Base URL: $BASE_URL                            ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Переменные для токенов
ACCESS_TOKEN=""
REFRESH_TOKEN=""
FILE_UUID=""
COUNTRY_UUID=""

# Функция для вывода заголовков
print_header() {
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# Функция для проверки HTTP кода
check_response() {
    local http_code=$1
    local expected=$2
    local description=$3
    
    if [ "$http_code" == "$expected" ]; then
        echo -e "${GREEN}✅ $description - HTTP $http_code${NC}"
        return 0
    else
        echo -e "${RED}❌ $description - HTTP $http_code (ожидался $expected)${NC}"
        return 1
    fi
}

# 0. Проверка доступности сервера
print_header "0️⃣  ПРОВЕРКА ДОСТУПНОСТИ СЕРВЕРА"

echo -n "Проверка главной страницы... "
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if [ "$http_code" == "200" ] && [ "$body" == "OK" ]; then
    echo -e "${GREEN}✅ Сервер доступен${NC}"
else
    echo -e "${RED}❌ Сервер недоступен (HTTP $http_code)${NC}"
    exit 1
fi
echo ""

# 1. Swagger UI
print_header "1️⃣  SWAGGER UI"

echo -n "Проверка Swagger UI... "
http_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/swagger-ui.html")
check_response "$http_code" "200" "Swagger UI доступен"

echo -n "Проверка OpenAPI спецификации... "
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/api-docs.json")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if [ "$http_code" == "200" ]; then
    # Проверяем что это валидный JSON
    if echo "$body" | jq . > /dev/null 2>&1; then
        tags=$(echo "$body" | jq -r '.tags[].name' | tr '\n' ', ')
        echo -e "${GREEN}✅ OpenAPI спецификация валидна${NC}"
        echo -e "   Доступные модули: ${YELLOW}$tags${NC}"
    else
        echo -e "${RED}❌ OpenAPI спецификация невалидна${NC}"
    fi
else
    echo -e "${RED}❌ Ошибка загрузки OpenAPI спецификации (HTTP $http_code)${NC}"
fi
echo ""

# 2. Identity Module - Регистрация
print_header "2️⃣  IDENTITY MODULE - РЕГИСТРАЦИЯ"

RANDOM_EMAIL="test$(date +%s)@example.com"
echo "Регистрация нового пользователя: $RANDOM_EMAIL"

response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/identity/registration" \
  -H "Content-Type: application/json" \
  -d "{\"login\":\"$RANDOM_EMAIL\",\"password\":\"123456\"}")

http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if check_response "$http_code" "201" "Регистрация пользователя"; then
    echo "$body" | jq . 2>/dev/null || echo "$body"
else
    echo "$body"
fi
echo ""

# 3. Identity Module - Логин
print_header "3️⃣  IDENTITY MODULE - ЛОГИН"

echo "Логин с учетными данными..."
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/identity/login" \
  -H "Content-Type: application/json" \
  -d "{\"login\":\"$RANDOM_EMAIL\",\"password\":\"123456\"}")

http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if check_response "$http_code" "200" "Логин"; then
    ACCESS_TOKEN=$(echo "$body" | jq -r '.access_token')
    REFRESH_TOKEN=$(echo "$body" | jq -r '.refresh_token')
    echo -e "${YELLOW}Access Token: ${ACCESS_TOKEN:0:50}...${NC}"
    echo -e "${YELLOW}Refresh Token: ${REFRESH_TOKEN:0:50}...${NC}"
    echo "$body" | jq .
else
    echo "$body"
fi
echo ""

# 4. Identity Module - Refresh Token
print_header "4️⃣  IDENTITY MODULE - REFRESH TOKEN"

if [ -n "$ACCESS_TOKEN" ] && [ -n "$REFRESH_TOKEN" ]; then
    echo "Обновление токенов..."
    response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/identity/refresh" \
      -H "Content-Type: application/json" \
      -d "{\"access_token\":\"$ACCESS_TOKEN\",\"refresh_token\":\"$REFRESH_TOKEN\"}")
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if check_response "$http_code" "200" "Refresh токенов"; then
        ACCESS_TOKEN=$(echo "$body" | jq -r '.access_token')
        REFRESH_TOKEN=$(echo "$body" | jq -r '.refresh_token')
        echo "$body" | jq .
    else
        echo "$body"
    fi
else
    echo -e "${YELLOW}⚠️  Пропущено (нет токенов)${NC}"
fi
echo ""

# 5. Reference Module - Список стран
print_header "5️⃣  REFERENCE MODULE - СПИСОК СТРАН"

echo "Получение списка стран..."
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/reference/country")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if check_response "$http_code" "200" "Список стран"; then
    count=$(echo "$body" | jq '. | length')
    echo -e "   Найдено стран: ${GREEN}$count${NC}"
    echo "$body" | jq '.[0:3]'  # Показываем первые 3
else
    echo "$body"
fi
echo ""

# 6. Reference Module - Создание страны
print_header "6️⃣  REFERENCE MODULE - СОЗДАНИЕ СТРАНЫ"

RANDOM_CODE="T$(date +%s | tail -c 2)"
echo "Создание новой страны (код: $RANDOM_CODE)..."
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/reference/country" \
  -H "Content-Type: application/json" \
  -d "{\"code\":\"$RANDOM_CODE\",\"name\":\"Тестовая страна\",\"sortOrder\":100}")

http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if check_response "$http_code" "201" "Создание страны"; then
    COUNTRY_UUID=$(echo "$body" | jq -r '.uuid')
    echo -e "   UUID: ${YELLOW}$COUNTRY_UUID${NC}"
    echo "$body" | jq .
else
    echo "$body"
fi
echo ""

# 7. Reference Module - Обновление страны
print_header "7️⃣  REFERENCE MODULE - ОБНОВЛЕНИЕ СТРАНЫ"

if [ -n "$COUNTRY_UUID" ]; then
    echo "Обновление страны $COUNTRY_UUID..."
    response=$(curl -s -w "\n%{http_code}" -X PUT "$BASE_URL/reference/country/$COUNTRY_UUID" \
      -H "Content-Type: application/json" \
      -d "{\"name\":\"Тестовая страна (обновлено)\",\"sortOrder\":99}")
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    check_response "$http_code" "200" "Обновление страны"
    echo "$body" | jq . 2>/dev/null || echo "$body"
else
    echo -e "${YELLOW}⚠️  Пропущено (нет UUID страны)${NC}"
fi
echo ""

# 8. Storage Module - Загрузка файла
print_header "8️⃣  STORAGE MODULE - ЗАГРУЗКА ФАЙЛА"

echo "Создание тестового файла..."
echo "Это тестовый файл для проверки API" > /tmp/test-file.txt

echo "Загрузка файла на сервер..."
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/storage/files" \
  -F "file=@/tmp/test-file.txt")

http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if check_response "$http_code" "201" "Загрузка файла"; then
    FILE_UUID=$(echo "$body" | jq -r '.uuid')
    echo -e "   UUID файла: ${YELLOW}$FILE_UUID${NC}"
    echo "$body" | jq .
else
    echo "$body"
fi

rm -f /tmp/test-file.txt
echo ""

# 9. Storage Module - Список файлов
print_header "9️⃣  STORAGE MODULE - СПИСОК ФАЙЛОВ"

echo "Получение списка файлов..."
response=$(curl -s -w "\n%{http_code}" "$BASE_URL/storage/files")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if check_response "$http_code" "200" "Список файлов"; then
    count=$(echo "$body" | jq '. | length')
    echo -e "   Найдено файлов: ${GREEN}$count${NC}"
    echo "$body" | jq '.[0:3]'  # Показываем первые 3
else
    echo "$body"
fi
echo ""

# 10. Storage Module - Скачивание файла
print_header "🔟 STORAGE MODULE - СКАЧИВАНИЕ ФАЙЛА"

if [ -n "$FILE_UUID" ]; then
    echo "Скачивание файла $FILE_UUID..."
    http_code=$(curl -s -o /tmp/downloaded-file.txt -w "%{http_code}" "$BASE_URL/storage/files/$FILE_UUID")
    
    if check_response "$http_code" "200" "Скачивание файла"; then
        size=$(wc -c < /tmp/downloaded-file.txt | tr -d ' ')
        echo -e "   Размер файла: ${GREEN}$size байт${NC}"
        echo -e "   Содержимое: $(cat /tmp/downloaded-file.txt)"
    fi
    
    rm -f /tmp/downloaded-file.txt
else
    echo -e "${YELLOW}⚠️  Пропущено (нет UUID файла)${NC}"
fi
echo ""

# 11. Storage Module - Удаление файла
print_header "1️⃣1️⃣ STORAGE MODULE - УДАЛЕНИЕ ФАЙЛА"

if [ -n "$FILE_UUID" ]; then
    echo "Удаление файла $FILE_UUID..."
    http_code=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/storage/files/$FILE_UUID")
    check_response "$http_code" "204" "Удаление файла"
else
    echo -e "${YELLOW}⚠️  Пропущено (нет UUID файла)${NC}"
fi
echo ""

# 12. Reference Module - Удаление страны
print_header "1️⃣2️⃣ REFERENCE MODULE - УДАЛЕНИЕ СТРАНЫ"

if [ -n "$COUNTRY_UUID" ]; then
    echo "Удаление тестовой страны $COUNTRY_UUID..."
    http_code=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/reference/country/$COUNTRY_UUID")
    check_response "$http_code" "204" "Удаление страны"
else
    echo -e "${YELLOW}⚠️  Пропущено (нет UUID страны)${NC}"
fi
echo ""

# Итоги
print_header "📊 ИТОГИ ТЕСТИРОВАНИЯ"

echo ""
echo -e "${GREEN}✅ Тестирование завершено!${NC}"
echo ""
echo "Проверено:"
echo "  • Health check (GET /)"
echo "  • Swagger UI и OpenAPI спецификация"
echo "  • Identity: регистрация, логин, refresh токенов"
echo "  • Reference: список, создание, обновление, удаление стран"
echo "  • Storage: загрузка, список, скачивание, удаление файлов"
echo ""
echo -e "${BLUE}Swagger UI: $BASE_URL/swagger-ui.html${NC}"
echo ""
