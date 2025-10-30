<?php

class JWTService {

    private $secretKey;

    public function __construct(Config $config) {
        $this->secretKey = $config->secretKey;
    }

    public function generateToken() {
        $accessToken = $this->getAccessToken($this->secretKey);
        $refreshToken = $this->getRefreshToken($this->secretKey);
        $expiresIn = strtotime("+1 day");

        $token = new Token($accessToken, $refreshToken, $expiresIn);

        return $token;
    }

    private function getAccessToken($secretKey) {
        $payload = ['type','accessToken'];
        $token = $this->generateJWT($payload, $secretKey);
        return $token;
    }

    private function getRefreshToken($secretKey) {
        $payload = ['type','refreshToken'];
        $token = $this->generateJWT($payload, $secretKey);
        return $token;
    }

      function generateJWT($payload, $secretKey, $algorithm = 'sha256') {
        $header = json_encode(['typ' => 'JWT', 'alg' => $algorithm]);
        $payload = json_encode($payload);

        // Encode Header
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Create Signature Hash
        $signature = hash_hmac($algorithm, $base64UrlHeader . "." . $base64UrlPayload, $secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Concatenate Header, Payload, and Signature
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }

    function decodeJWT($jwt, $secretKey) {
    // Разбиваем JWT на три части: Header, Payload, Signature
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = explode('.', $jwt);

        // Декодируем Payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);

        // Проверяем подпись (необязательно, но рекомендуется)
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlSignature));
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $secretKey, true);

        if (hash_equals($signature, $expectedSignature)) {
            return $payload;
        } else {
            // Подпись не совпадает, возможно, токен был изменен
            return null;
        }
    }
}