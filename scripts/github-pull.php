<?php

declare(strict_types=1);

/**
 * git pull с GitHub
 *
 * CLI:  php scripts/github-pull.php
 * HTTP: GET/POST /github-pull.php?token=SECRET
 *       или Header: X-Deploy-Token: SECRET
 */

$projectRoot = dirname(__DIR__);
$configFile = $projectRoot . '/config/github-pull.local.php';

$config = is_file($configFile) ? require $configFile : [];
$secret = (string) ($config['secret'] ?? '');
$branch = (string) ($config['branch'] ?? 'master');

function githubPull(string $projectRoot, string $branch): array
{
    if (!is_dir($projectRoot . '/.git')) {
        throw new RuntimeException('Git repo not found. Run: git clone git@github.com:user/repo.git .');
    }
    if (!function_exists('exec')) {
        throw new RuntimeException('exec() is disabled on this hosting');
    }

    $cmd = 'cd ' . escapeshellarg($projectRoot)
        . ' && git pull origin ' . escapeshellarg($branch) . ' 2>&1';

    $output = [];
    $code = 0;
    exec($cmd, $output, $code);

    if ($code !== 0) {
        throw new RuntimeException("git pull failed:\n" . implode("\n", $output));
    }

    return $output;
}

function checkToken(string $expected): void
{
    if ($expected === '' || $expected === 'change-me') {
        throw new RuntimeException('Set secret in config/github-pull.local.php');
    }

    $provided = $_SERVER['HTTP_X_DEPLOY_TOKEN']
        ?? $_GET['token']
        ?? '';

    if (!hash_equals($expected, (string) $provided)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Invalid token']);
        exit;
    }
}

if (PHP_SAPI === 'cli') {
    $output = githubPull($projectRoot, $branch);
    echo "git pull origin {$branch}\n";
    echo implode("\n", $output) . "\n";
    exit(0);
}

header('Content-Type: application/json; charset=utf-8');

try {
    checkToken($secret);
    $output = githubPull($projectRoot, $branch);
    echo json_encode([
        'ok' => true,
        'branch' => $branch,
        'log' => $output,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
