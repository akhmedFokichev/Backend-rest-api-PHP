<?php
/**
 * Тестирование API эндпоинтов
 * Запустите на сервере: php test-api.php
 * Или откройте в браузере: https://tradeapp.xsdk.ru/test-api.php
 */

header('Content-Type: text/html; charset=utf-8');

$baseUrl = 'https://tradeapp.xsdk.ru';
$results = [];

function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $body = substr($response, $headerSize);
    $jsonData = json_decode($body, true);
    
    return [
        'httpCode' => $httpCode,
        'body' => $body,
        'json' => $jsonData,
        'error' => $error
    ];
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестирование API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #666; }
        .test.success { border-left-color: #4CAF50; }
        .test.error { border-left-color: #f44336; }
        .test.warning { border-left-color: #ff9800; }
        .method { display: inline-block; padding: 4px 8px; border-radius: 3px; font-weight: bold; color: white; margin-right: 10px; }
        .method.get { background: #61affe; }
        .method.post { background: #49cc90; }
        .method.put { background: #fca130; }
        .method.delete { background: #f93e3e; }
        pre { background: #f8f8f8; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .status { font-weight: bold; }
        .status.ok { color: #4CAF50; }
        .status.error { color: #f44336; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>🧪 Тестирование Backend REST API</h1>
    <p><strong>База:</strong> <?= htmlspecialchars($baseUrl) ?></p>

    <?php
    // 1. Проверка здоровья API
    echo "<h2>1. Проверка доступности</h2>";
    $result = makeRequest($baseUrl . '/');
    $class = $result['httpCode'] === 200 ? 'success' : 'error';
    $statusClass = $result['httpCode'] === 200 ? 'ok' : 'error';
    ?>
    <div class="test <?= $class ?>">
        <span class="method get">GET</span>
        <strong>/</strong>
        <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
        <pre><?= htmlspecialchars($result['body']) ?></pre>
    </div>

    <?php
    // 2. Тест регистрации
    echo "<h2>2. Identity: Регистрация</h2>";
    $testUser = [
        'login' => 'test_' . time() . '@example.com',
        'password' => 'Test123456!'
    ];
    $result = makeRequest($baseUrl . '/identity/registration', 'POST', $testUser);
    $class = in_array($result['httpCode'], [201, 409]) ? 'success' : 'error';
    $statusClass = in_array($result['httpCode'], [201, 409]) ? 'ok' : 'error';
    $userId = $result['json']['id'] ?? null;
    ?>
    <div class="test <?= $class ?>">
        <span class="method post">POST</span>
        <strong>/identity/registration</strong>
        <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
        <p><strong>Запрос:</strong></p>
        <pre><?= htmlspecialchars(json_encode($testUser, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <p><strong>Ответ:</strong></p>
        <pre><?= htmlspecialchars(json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php if ($result['httpCode'] === 201): ?>
            <p style="color: green;">✅ Пользователь успешно зарегистрирован</p>
        <?php elseif ($result['httpCode'] === 409): ?>
            <p style="color: orange;">⚠️ Пользователь уже существует (ожидаемое поведение)</p>
        <?php endif; ?>
    </div>

    <?php
    // 3. Тест логина
    echo "<h2>3. Identity: Вход</h2>";
    $result = makeRequest($baseUrl . '/identity/login', 'POST', $testUser);
    $class = $result['httpCode'] === 200 ? 'success' : 'error';
    $statusClass = $result['httpCode'] === 200 ? 'ok' : 'error';
    $accessToken = $result['json']['accessToken'] ?? null;
    $refreshToken = $result['json']['refreshToken'] ?? null;
    ?>
    <div class="test <?= $class ?>">
        <span class="method post">POST</span>
        <strong>/identity/login</strong>
        <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
        <p><strong>Запрос:</strong></p>
        <pre><?= htmlspecialchars(json_encode($testUser, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <p><strong>Ответ:</strong></p>
        <pre><?= htmlspecialchars(json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php if ($accessToken): ?>
            <p style="color: green;">✅ Получен JWT токен</p>
        <?php endif; ?>
    </div>

    <?php
    // 4. Тест обновления токена
    if ($refreshToken) {
        echo "<h2>4. Identity: Обновление токена</h2>";
        $refreshData = [
            'login' => $testUser['login'],
            'refreshToken' => $refreshToken
        ];
        $result = makeRequest($baseUrl . '/identity/refresh', 'POST', $refreshData);
        $class = $result['httpCode'] === 200 ? 'success' : 'error';
        $statusClass = $result['httpCode'] === 200 ? 'ok' : 'error';
        ?>
        <div class="test <?= $class ?>">
            <span class="method post">POST</span>
            <strong>/identity/refresh</strong>
            <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
            <p><strong>Ответ:</strong></p>
            <pre><?= htmlspecialchars(json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            <?php if ($result['httpCode'] === 200): ?>
                <p style="color: green;">✅ Токен успешно обновлен</p>
            <?php endif; ?>
        </div>
        <?php
    }

    // 5. Тест получения списка стран
    echo "<h2>5. Reference: Получение списка стран</h2>";
    $result = makeRequest($baseUrl . '/reference/country', 'GET');
    $class = $result['httpCode'] === 200 ? 'success' : 'error';
    $statusClass = $result['httpCode'] === 200 ? 'ok' : 'error';
    $countries = $result['json'] ?? [];
    ?>
    <div class="test <?= $class ?>">
        <span class="method get">GET</span>
        <strong>/reference/country</strong>
        <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
        <p><strong>Ответ:</strong></p>
        <pre><?= htmlspecialchars(json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php if ($result['httpCode'] === 200): ?>
            <p style="color: green;">✅ Получено записей: <?= count($countries) ?></p>
        <?php endif; ?>
    </div>

    <?php
    // 6. Тест создания страны
    echo "<h2>6. Reference: Создание страны</h2>";
    $testCountry = [
        'code' => 'TEST',
        'name' => 'Тестовая страна ' . time(),
        'is_catalog' => false,
        'sort_order' => 0
    ];
    $result = makeRequest($baseUrl . '/reference/country', 'POST', $testCountry);
    $class = $result['httpCode'] === 201 ? 'success' : 'error';
    $statusClass = $result['httpCode'] === 201 ? 'ok' : 'error';
    $createdUuid = $result['json']['uuid'] ?? null;
    ?>
    <div class="test <?= $class ?>">
        <span class="method post">POST</span>
        <strong>/reference/country</strong>
        <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
        <p><strong>Запрос:</strong></p>
        <pre><?= htmlspecialchars(json_encode($testCountry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <p><strong>Ответ:</strong></p>
        <pre><?= htmlspecialchars(json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php if ($createdUuid): ?>
            <p style="color: green;">✅ Страна создана, UUID: <?= htmlspecialchars($createdUuid) ?></p>
        <?php endif; ?>
    </div>

    <?php
    // 7. Тест обновления страны
    if ($createdUuid) {
        echo "<h2>7. Reference: Обновление страны</h2>";
        $updateData = [
            'name' => 'Обновленная страна ' . time()
        ];
        $result = makeRequest($baseUrl . '/reference/country/' . $createdUuid, 'PUT', $updateData);
        $class = $result['httpCode'] === 204 ? 'success' : 'error';
        $statusClass = $result['httpCode'] === 204 ? 'ok' : 'error';
        ?>
        <div class="test <?= $class ?>">
            <span class="method put">PUT</span>
            <strong>/reference/country/{uuid}</strong>
            <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
            <p><strong>UUID:</strong> <?= htmlspecialchars($createdUuid) ?></p>
            <p><strong>Запрос:</strong></p>
            <pre><?= htmlspecialchars(json_encode($updateData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            <?php if ($result['httpCode'] === 204): ?>
                <p style="color: green;">✅ Страна успешно обновлена</p>
            <?php endif; ?>
        </div>
        <?php

        // 8. Тест удаления страны
        echo "<h2>8. Reference: Удаление страны</h2>";
        $result = makeRequest($baseUrl . '/reference/country/' . $createdUuid, 'DELETE');
        $class = $result['httpCode'] === 204 ? 'success' : 'error';
        $statusClass = $result['httpCode'] === 204 ? 'ok' : 'error';
        ?>
        <div class="test <?= $class ?>">
            <span class="method delete">DELETE</span>
            <strong>/reference/country/{uuid}</strong>
            <span class="status <?= $statusClass ?>">HTTP <?= $result['httpCode'] ?></span>
            <p><strong>UUID:</strong> <?= htmlspecialchars($createdUuid) ?></p>
            <?php if ($result['httpCode'] === 204): ?>
                <p style="color: green;">✅ Страна успешно удалена</p>
            <?php endif; ?>
        </div>
        <?php
    }
    ?>

    <h2>📊 Итоги тестирования</h2>
    <p>Все основные эндпоинты API протестированы.</p>
    <p><a href="<?= $baseUrl ?>/swagger-ui.html" target="_blank">Открыть Swagger UI для ручного тестирования</a></p>
</body>
</html>
