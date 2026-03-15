#!/bin/bash
# Быстрая проверка API — работает без jq
# Запуск: ./test-api-quick.sh   или   ./test-api-quick.sh https://tradeapp.xsdk.ru

BASE_URL="${1:-https://tradeapp.xsdk.ru}"
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'

echo "╔════════════════════════════════════════════════════════╗"
echo "║         Быстрая проверка API — $BASE_URL"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

ok=0
fail=0

check() {
    local name="$1"
    local method="$2"
    local url="$3"
    local data="$4"
    local want_code="${5:-200}"
    local code
    if [ -z "$data" ]; then
        code=$(curl -s -o /tmp/api_body.txt -w "%{http_code}" -X "$method" "$url")
    else
        code=$(curl -s -o /tmp/api_body.txt -w "%{http_code}" -X "$method" "$url" -H "Content-Type: application/json" -d "$data")
    fi
    if [ "$code" = "$want_code" ]; then
        echo -e "${GREEN}✅ $name — HTTP $code${NC}"
        ok=$((ok+1))
        return 0
    else
        echo -e "${RED}❌ $name — HTTP $code (ожидался $want_code)${NC}"
        fail=$((fail+1))
        return 1
    fi
}

echo -e "${BLUE}[1] Health${NC}"
check "GET /" "GET" "$BASE_URL/" "" "200"
echo ""

echo -e "${BLUE}[2] Swagger UI${NC}"
check "GET /swagger-ui.html" "GET" "$BASE_URL/swagger-ui.html" "" "200"
echo ""

echo -e "${BLUE}[3] OpenAPI spec${NC}"
check "GET /api-docs.json" "GET" "$BASE_URL/api-docs.json" "" "200"
echo ""

echo -e "${BLUE}[4] Identity — регистрация${NC}"
EMAIL="test$(date +%s)@example.com"
check "POST /identity/registration" "POST" "$BASE_URL/identity/registration" "{\"login\":\"$EMAIL\",\"password\":\"123456\"}" "201"
echo ""

echo -e "${BLUE}[5] Identity — логин${NC}"
check "POST /identity/login" "POST" "$BASE_URL/identity/login" "{\"login\":\"$EMAIL\",\"password\":\"123456\"}" "200"
echo ""

echo -e "${BLUE}[6] Reference — список стран${NC}"
check "GET /reference/country" "GET" "$BASE_URL/reference/country" "" "200"
echo ""

echo -e "${BLUE}[7] Reference — создание страны${NC}"
CODE="X$(date +%s | tail -c 3)"
resp=$(curl -s -X POST "$BASE_URL/reference/country" -H "Content-Type: application/json" -d "{\"code\":\"$CODE\",\"name\":\"Тест\",\"sortOrder\":999}")
if echo "$resp" | grep -q '"uuid"'; then
    echo -e "${GREEN}✅ POST /reference/country — HTTP 201${NC}"
    ok=$((ok+1))
    COUNTRY_UUID=$(echo "$resp" | sed -n 's/.*"uuid":"\([^"]*\)".*/\1/p')
else
    echo -e "${RED}❌ POST /reference/country — ответ не содержит uuid${NC}"
    fail=$((fail+1))
fi
echo ""

echo -e "${BLUE}[8] Storage — загрузка файла${NC}"
echo "test content $(date)" > /tmp/test_upload.txt
code=$(curl -s -o /tmp/api_body.txt -w "%{http_code}" -X POST "$BASE_URL/storage/files" -F "file=@/tmp/test_upload.txt")
if [ "$code" = "201" ]; then
    echo -e "${GREEN}✅ POST /storage/files — HTTP 201${NC}"
    ok=$((ok+1))
    FILE_UUID=$(grep -o '"uuid":"[^"]*"' /tmp/api_body.txt | head -1 | cut -d'"' -f4)
else
    echo -e "${RED}❌ POST /storage/files — HTTP $code${NC}"
    fail=$((fail+1))
fi
rm -f /tmp/test_upload.txt
echo ""

echo -e "${BLUE}[9] Storage — список файлов${NC}"
check "GET /storage/files" "GET" "$BASE_URL/storage/files" "" "200"
echo ""

echo -e "${BLUE}[10] Storage — скачивание${NC}"
if [ -n "$FILE_UUID" ]; then
    code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/storage/files/$FILE_UUID")
    if [ "$code" = "200" ]; then
        echo -e "${GREEN}✅ GET /storage/files/{uuid} — HTTP 200${NC}"
        ok=$((ok+1))
    else
        echo -e "${RED}❌ GET /storage/files/{uuid} — HTTP $code${NC}"
        fail=$((fail+1))
    fi
else
    echo -e "${YELLOW}⏭️  GET /storage/files/{uuid} — пропущено (нет uuid)${NC}"
fi
echo ""

echo -e "${BLUE}[11] Storage — удаление файла${NC}"
if [ -n "$FILE_UUID" ]; then
    code=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/storage/files/$FILE_UUID")
    if [ "$code" = "204" ]; then
        echo -e "${GREEN}✅ DELETE /storage/files/{uuid} — HTTP 204${NC}"
        ok=$((ok+1))
    else
        echo -e "${RED}❌ DELETE /storage/files/{uuid} — HTTP $code${NC}"
        fail=$((fail+1))
    fi
else
    echo -e "${YELLOW}⏭️  DELETE /storage/files/{uuid} — пропущено${NC}"
fi
echo ""

echo -e "${BLUE}[12] Reference — удаление страны${NC}"
if [ -n "$COUNTRY_UUID" ]; then
    code=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/reference/country/$COUNTRY_UUID")
    if [ "$code" = "204" ]; then
        echo -e "${GREEN}✅ DELETE /reference/country/{uuid} — HTTP 204${NC}"
        ok=$((ok+1))
    else
        echo -e "${RED}❌ DELETE /reference/country/{uuid} — HTTP $code${NC}"
        fail=$((fail+1))
    fi
else
    echo -e "${YELLOW}⏭️  DELETE /reference/country/{uuid} — пропущено${NC}"
fi
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "Итого: ${GREEN}$ok успешно${NC}, ${RED}$fail с ошибками${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
[ "$fail" -eq 0 ] && echo -e "${GREEN}Все проверки пройдены.${NC}" || echo -e "${RED}Есть ошибки.${NC}"
exit $fail
