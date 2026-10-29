<?php
declare(strict_types=1);
require __DIR__ . '/_common.php';
$config = demo_config();
require_admin($config);
$dir = ensure_data_dir($config);
$events = jsonl_rows($dir . '/events.jsonl');
$feedback = jsonl_rows($dir . '/feedback.jsonl');
$sessions = [];
$eventCounts = [];
$screenViews = [];
foreach ($events as $row) {
    if (!empty($row['session_id'])) $sessions[$row['session_id']] = true;
    $name = $row['event'] ?? 'unknown';
    $eventCounts[$name] = ($eventCounts[$name] ?? 0) + 1;
    if ($name === 'screen_view') {
        $screen = $row['data']['name'] ?? $row['screen'] ?? 'unknown';
        $screenViews[$screen] = ($screenViews[$screen] ?? 0) + 1;
    }
}
$ratingSums = ['design'=>0,'clarity'=>0,'usefulness'=>0];
$wouldUse = ['yes'=>0,'maybe'=>0,'no'=>0];
foreach ($feedback as $row) {
    foreach ($ratingSums as $key => $sum) $ratingSums[$key] += (int)($row['ratings'][$key] ?? 0);
    $answer = $row['would_use'] ?? 'maybe';
    $wouldUse[$answer] = ($wouldUse[$answer] ?? 0) + 1;
}
$count = count($feedback);
$ratings = [];
foreach ($ratingSums as $key => $sum) $ratings[$key] = $count ? round($sum / $count, 2) : 0;
json_response([
    'generated_at' => gmdate('c'),
    'sessions' => count($sessions),
    'events_total' => count($events),
    'feedback_total' => $count,
    'demo_registrations' => $eventCounts['event_register_demo'] ?? 0,
    'ratings' => $ratings,
    'would_use' => $wouldUse,
    'event_counts' => $eventCounts,
    'screen_views' => $screenViews,
]);
