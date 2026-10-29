<?php
declare(strict_types=1);
require __DIR__ . '/_common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$config = demo_config();
$input = read_json_body();
$ratings = [];
foreach (['design','clarity','usefulness'] as $key) {
    $value = (int)($input['ratings'][$key] ?? 0);
    if ($value < 1 || $value > 5) json_response(['error' => 'Заповніть усі оцінки'], 422);
    $ratings[$key] = $value;
}
$features = array_values(array_intersect((array)($input['features'] ?? []), ['events','profile','community','ai']));
$wouldUse = clean_text($input['would_use'] ?? '', 10);
if (!in_array($wouldUse, ['yes','maybe','no'], true)) $wouldUse = 'maybe';
$row = [
    'id' => bin2hex(random_bytes(8)),
    'session_id' => clean_text($input['session_id'] ?? '', 80),
    'ratings' => $ratings,
    'features' => $features,
    'comment' => clean_text($input['comment'] ?? '', 500),
    'would_use' => $wouldUse,
    'duration_seconds' => max(0, min(7200, (int)($input['duration_seconds'] ?? 0))),
    'ts' => gmdate('c'),
];
append_jsonl(ensure_data_dir($config) . '/feedback.jsonl', $row);

$email = (string)($config['admin_email'] ?? '');
if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && !str_contains($email, 'example.com')) {
    $subject = 'Новий анонімний відгук про демо';
    $body = "Дизайн: {$ratings['design']}/5\nЗрозумілість: {$ratings['clarity']}/5\nКорисність: {$ratings['usefulness']}/5\nКористувався/лася б: {$wouldUse}\nФункції: " . implode(', ', $features) . "\nКоментар: {$row['comment']}\n";
    @mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, 'From: ' . ($config['mail_from'] ?? 'demo@localhost'));
}
json_response(['ok' => true], 201);
