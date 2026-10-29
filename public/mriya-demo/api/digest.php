<?php
declare(strict_types=1);
require __DIR__ . '/_common.php';
$config = demo_config();
require_admin($config);
$dir = ensure_data_dir($config);
$events = jsonl_rows($dir . '/events.jsonl');
$feedback = jsonl_rows($dir . '/feedback.jsonl');
$since = time() - 86400;
$recentEvents = array_filter($events, fn($r) => strtotime($r['ts'] ?? '') >= $since);
$recentFeedback = array_filter($feedback, fn($r) => strtotime($r['ts'] ?? '') >= $since);
$email = (string)($config['admin_email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || str_contains($email, 'example.com')) json_response(['error' => 'Налаштуйте admin_email'], 422);
$subject = 'Щоденна аналітика демо';
$body = "За останні 24 години:\nВзаємодії: " . count($recentEvents) . "\nВідгуки: " . count($recentFeedback) . "\n\nПовна аналітика: відкрийте admin.html\n";
$sent = @mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, 'From: ' . ($config['mail_from'] ?? 'demo@localhost'));
json_response(['ok' => $sent, 'events' => count($recentEvents), 'feedback' => count($recentFeedback)]);
