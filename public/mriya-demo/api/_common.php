<?php
declare(strict_types=1);

function demo_config(): array {
    $path = __DIR__ . '/config.php';
    if (is_file($path)) return require $path;
    return require __DIR__ . '/config.example.php';
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 20000) json_response(['error' => 'Некоректний запит'], 400);
    $data = json_decode($raw, true);
    if (!is_array($data)) json_response(['error' => 'Очікується JSON'], 400);
    return $data;
}

function clean_text(mixed $value, int $max = 500): string {
    $text = trim(strip_tags((string)$value));
    return mb_substr($text, 0, $max);
}

function ensure_data_dir(array $config): string {
    $dir = $config['data_dir'];
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) json_response(['error' => 'Сховище недоступне'], 500);
    return $dir;
}

function append_jsonl(string $path, array $row): void {
    $line = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    if (file_put_contents($path, $line, FILE_APPEND | LOCK_EX) === false) json_response(['error' => 'Не вдалося зберегти'], 500);
}

function require_admin(array $config): void {
    $provided = (string)($_GET['token'] ?? $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '');
    if (!$provided || !hash_equals((string)$config['admin_token'], $provided)) json_response(['error' => 'Немає доступу'], 403);
}

function jsonl_rows(string $path): array {
    if (!is_file($path)) return [];
    $rows = [];
    $handle = fopen($path, 'rb');
    if (!$handle) return [];
    while (($line = fgets($handle)) !== false) {
        $row = json_decode($line, true);
        if (is_array($row)) $rows[] = $row;
    }
    fclose($handle);
    return $rows;
}
