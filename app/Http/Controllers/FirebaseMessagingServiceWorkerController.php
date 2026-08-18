<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class FirebaseMessagingServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $config = [
            'apiKey' => config('services.firebase.web.api_key'),
            'authDomain' => config('services.firebase.web.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'messagingSenderId' => config('services.firebase.web.messaging_sender_id'),
            'appId' => config('services.firebase.web.app_id'),
        ];
        $json = json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $javascript = <<<JS
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

const firebaseConfig = {$json};
if (firebaseConfig.apiKey && firebaseConfig.projectId && firebaseConfig.messagingSenderId && firebaseConfig.appId) {
    firebase.initializeApp(firebaseConfig);
    firebase.messaging();
}
JS;

        return response($javascript, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'Service-Worker-Allowed' => '/',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
