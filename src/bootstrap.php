<?php
declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k,$v] = array_map('trim', explode('=', $line, 2)); $_ENV[$k] = trim($v, "\\\"'");
    }
}
function env(string $key, string $default=''): string { return (string)($_ENV[$key] ?? $default); }
function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', env('DB_HOST','127.0.0.1'), env('DB_PORT','3306'), env('DB_NAME','task_api')),
        env('DB_USER','root'), env('DB_PASS',''),
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]
    );
    return $pdo;
}
function jsonResponse(mixed $data, int $status=200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
}
function body(): array { $raw = file_get_contents('php://input') ?: ''; $data = json_decode($raw, true); return is_array($data) ? $data : []; }
function authenticate(): void {
    $header = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $expected = env('API_KEY');
    if ($expected === '' || !hash_equals($expected, $header)) jsonResponse(['error'=>'Unauthorized','message'=>'Envie uma API key válida em X-API-Key.'], 401);
}
function validateTask(array $data): array {
    $title = trim((string)($data['title'] ?? ''));
    $priority = $data['priority'] ?? 'medium'; $status = $data['status'] ?? 'todo'; $due = $data['due_date'] ?? null;
    $errors = [];
    if ($title === '') $errors['title'] = 'Título é obrigatório.';
    if (!in_array($priority, ['low','medium','high'], true)) $errors['priority'] = 'Prioridade inválida.';
    if (!in_array($status, ['todo','doing','done'], true)) $errors['status'] = 'Status inválido.';
    if ($due !== null && $due !== '' && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', (string)$due)) $errors['due_date'] = 'Use YYYY-MM-DD.';
    return [$errors, [$title, (string)($data['description'] ?? ''), $priority, $status, ($due === '' ? null : $due)]];
}
