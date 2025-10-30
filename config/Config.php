<?php

class Config
{
    // Info
    public $appName;
    public $version;

    // Identity
    public $hashKey;
    public $clientIds;
    public $secretKey;

    // Database
    public $host;
    public $db_name;
    public $username;
    public $password;

    public function __construct()
    {
        $fileEnv = $this->loadLocalEnv();

        $this->appName = $this->env('APP_NAME', 'Puma', $fileEnv);
        $this->version = $this->env('APP_VERSION', '0.0.1', $fileEnv);

        $this->hashKey = $this->env('HASH_KEY', 'change-me', $fileEnv);
        $this->clientIds = $this->parseCsvEnv('CLIENT_IDS', 'web_app', $fileEnv);
        $this->secretKey = $this->env('JWT_SECRET', 'change-me-secret', $fileEnv);

        $this->host = $this->env('DB_HOST', 'localhost', $fileEnv);
        $this->db_name = $this->env('DB_NAME', '', $fileEnv);
        $this->username = $this->env('DB_USER', '', $fileEnv);
        $this->password = $this->env('DB_PASS', '', $fileEnv);
    }

    private function loadLocalEnv(): array
    {
        $path = __DIR__ . '/env.local.php';
        if (file_exists($path)) {
            $data = include $path;
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    private function env(string $key, string $default, array $fileEnv)
    {
        if (array_key_exists($key, $fileEnv) && $fileEnv[$key] !== '') {
            return $fileEnv[$key];
        }
        $val = getenv($key);
        return ($val !== false && $val !== '') ? $val : $default;
    }

    private function parseCsvEnv(string $key, string $default, array $fileEnv): array
    {
        $value = $fileEnv[$key] ?? getenv($key);
        if ($value === false || trim($value) === '') {
            return [$default];
        }
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== ''));
    }
}