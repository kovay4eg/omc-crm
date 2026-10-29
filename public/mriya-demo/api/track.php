<?php
declare(strict_types=1);
require __DIR__ . '/_common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$config = demo_config();
$input = read_json_body();
$allowed = [
    'session_start', 'screen_view', 'role_switch', 'welcome_name_entered', 'welcome_complete',
    'onboarding_step', 'onboarding_skip', 'onboarding_complete',
    'event_open', 'event_register_demo', 'event_saved', 'calendar_add_demo',
    'feed_filter', 'feed_mode_change', 'feed_like', 'feed_comments_open', 'feed_comment_demo', 'feed_post_demo',
    'ai_open', 'ai_prompt_demo', 'mira_question_demo',
    'team_interest_demo', 'wave_demo', 'certificate_open', 'interest_select', 'share_demo',
    'feedback_open', 'feedback_submit', 'rating_select', 'content_moderation_block',
    'center_create_event_open', 'center_event_created_demo', 'center_analytics_open',
    'qr_scanner_open', 'qr_camera_start', 'qr_camera_denied', 'qr_checkin_demo',
    'talent_invite_demo', 'organization_register_open', 'organization_registered_demo',
    'vacancy_create_open', 'vacancy_preview_demo', 'vacancy_created_demo', 'applicants_open_demo', 'candidate_filter',
    'career_apply_demo', 'career_consultation_demo', 'career_training_demo',
];
$event = clean_text($input['event'] ?? '', 60);
if (!in_array($event, $allowed, true)) json_response(['error' => 'Невідома подія'], 422);
$row = [
    'event' => $event,
    'session_id' => clean_text($input['session_id'] ?? '', 80),
    'screen' => clean_text($input['screen'] ?? '', 40),
    'ts' => clean_text($input['ts'] ?? gmdate('c'), 40),
    'data' => is_array($input['data'] ?? null) ? $input['data'] : [],
];
append_jsonl(ensure_data_dir($config) . '/events.jsonl', $row);
json_response(['ok' => true], 201);
