<x-filament-panels::page>
    <style>
        .omc-mail {
            --mail-accent: #3047e8;
            --mail-accent-dark: #17248f;
            --mail-accent-soft: #eef1ff;
            --mail-surface: #ffffff;
            --mail-surface-subtle: #f8fafc;
            --mail-border: #e2e8f0;
            --mail-text: #172033;
            --mail-muted: #64748b;
            --mail-danger: #dc2626;
            display: grid;
            gap: 1.25rem;
            color: var(--mail-text);
        }

        .dark .omc-mail {
            --mail-accent-soft: rgba(99, 102, 241, .14);
            --mail-surface: #171923;
            --mail-surface-subtle: #10121a;
            --mail-border: #303440;
            --mail-text: #f8fafc;
            --mail-muted: #aeb7c7;
        }

        .omc-mail,
        .omc-mail * { box-sizing: border-box; }

        .omc-mail__hero {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            overflow: hidden;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 1.4rem;
            color: #fff;
            background: linear-gradient(135deg, #3349eb 0%, #202bb0 58%, #111967 100%);
            box-shadow: 0 18px 40px rgba(30, 41, 145, .22);
        }

        .omc-mail__hero::after {
            position: absolute;
            right: -4rem;
            bottom: -6rem;
            width: 16rem;
            height: 16rem;
            content: '';
            border: 2.4rem solid rgba(255, 255, 255, .08);
            border-radius: 999px;
            pointer-events: none;
        }

        .omc-mail__hero-copy,
        .omc-mail__hero-action { position: relative; z-index: 1; }
        .omc-mail__eyebrow { margin: 0; color: #cbd3ff; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .omc-mail__hero h2 { margin: .28rem 0 0; color: #fff; font-size: clamp(1.35rem, 3vw, 1.8rem); font-weight: 800; line-height: 1.15; }
        .omc-mail__hero-description { max-width: 42rem; margin: .55rem 0 0; color: rgba(255,255,255,.78); font-size: .88rem; line-height: 1.55; }

        .omc-mail__error {
            padding: .9rem 1rem;
            border: 1px solid rgba(239, 68, 68, .35);
            border-radius: .9rem;
            color: #991b1b;
            background: #fef2f2;
            font-size: .88rem;
        }
        .dark .omc-mail__error { color: #fecaca; background: rgba(127, 29, 29, .28); }

        .omc-mail__push {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .9rem 1rem;
            border: 1px solid var(--mail-border);
            border-radius: 1rem;
            background: var(--mail-surface);
            box-shadow: 0 6px 22px rgba(15, 23, 42, .05);
        }
        .omc-mail__push[data-state="enabled"] { border-color: rgba(34, 197, 94, .45); background: rgba(34, 197, 94, .07); }
        .omc-mail__push[data-state="error"], .omc-mail__push[data-state="denied"] { border-color: rgba(239, 68, 68, .35); }
        .omc-mail__push-icon { width: 1.35rem; height: 1.35rem; flex: 0 0 auto; color: var(--mail-accent); }
        .omc-mail__push-copy { min-width: 0; flex: 1; }
        .omc-mail__push-title { margin: 0; color: var(--mail-text); font-size: .86rem; font-weight: 800; }
        .omc-mail__push-status { margin: .2rem 0 0; color: var(--mail-muted); font-size: .76rem; line-height: 1.45; }
        .omc-mail__push-button { flex: 0 0 auto; padding: .58rem .85rem; border: 0; border-radius: .7rem; color: #fff; background: var(--mail-accent); font-size: .76rem; font-weight: 800; cursor: pointer; }
        .omc-mail__push-button:disabled { cursor: wait; opacity: .6; }

        .omc-mail__layout {
            display: grid;
            grid-template-columns: 13.5rem minmax(18rem, .82fr) minmax(0, 1.35fr);
            gap: 1.25rem;
            min-width: 0;
        }

        .omc-mail__nav { padding: .75rem; }
        .omc-mail__nav-title { margin: .25rem .55rem .65rem; color: var(--mail-muted); font-size: .7rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
        .omc-mail__folder {
            display: flex;
            width: 100%;
            align-items: center;
            gap: .65rem;
            margin: .16rem 0;
            padding: .72rem .75rem;
            border: 0;
            border-radius: .75rem;
            color: var(--mail-text);
            text-align: left;
            background: transparent;
            cursor: pointer;
            transition: color .18s ease, background .18s ease;
        }
        .omc-mail__folder:hover { background: var(--mail-surface-subtle); }
        .omc-mail__folder.is-active { color: var(--mail-accent-dark); background: var(--mail-accent-soft); font-weight: 800; }
        .dark .omc-mail__folder.is-active { color: #c7d2fe; }
        .omc-mail__folder-icon { width: 1.2rem; height: 1.2rem; flex: 0 0 auto; }
        .omc-mail__folder-label { min-width: 0; flex: 1; }
        .omc-mail__folder-count { flex: 0 0 auto; color: var(--mail-muted); font-size: .72rem; font-weight: 700; }

        .omc-mail__filters { display: flex; gap: .35rem; padding: .7rem .85rem; overflow-x: auto; border-bottom: 1px solid var(--mail-border); }
        .omc-mail__filter { flex: 0 0 auto; padding: .44rem .7rem; border: 1px solid var(--mail-border); border-radius: 999px; color: var(--mail-muted); background: var(--mail-surface); font-size: .75rem; font-weight: 700; cursor: pointer; }
        .omc-mail__filter.is-active { border-color: transparent; color: #fff; background: var(--mail-accent); }

        .omc-mail__panel {
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--mail-border);
            border-radius: 1.15rem;
            background: var(--mail-surface);
            box-shadow: 0 8px 28px rgba(15, 23, 42, .06);
        }

        .omc-mail__toolbar {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .85rem;
            border-bottom: 1px solid var(--mail-border);
            background: var(--mail-surface-subtle);
        }

        .omc-mail__search,
        .omc-mail__field,
        .omc-mail__textarea {
            width: 100%;
            min-width: 0;
            border: 1px solid var(--mail-border);
            border-radius: .8rem;
            padding: .72rem .85rem;
            color: var(--mail-text);
            background: var(--mail-surface);
            font: inherit;
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .omc-mail__search:focus,
        .omc-mail__field:focus,
        .omc-mail__textarea:focus {
            border-color: var(--mail-accent);
            box-shadow: 0 0 0 3px rgba(48, 71, 232, .14);
        }

        .omc-mail__list { max-height: 65vh; overflow-y: auto; }
        .omc-mail__message {
            display: block;
            width: 100%;
            padding: 1rem;
            border: 0;
            border-bottom: 1px solid var(--mail-border);
            color: var(--mail-text);
            text-align: left;
            background: transparent;
            cursor: pointer;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            transition: background .18s ease;
        }
        .omc-mail__message:focus-visible,
        .omc-mail__message:active,
        .omc-mail__message.is-opening { background: var(--mail-accent-soft); outline: none; }
        .omc-mail__message.is-opening { cursor: progress; }
        @media (hover: hover) and (pointer: fine) {
            .omc-mail__message:hover { background: var(--mail-accent-soft); }
        }
        .omc-mail__message-row { display: flex; align-items: flex-start; gap: .75rem; }
        .omc-mail__unread-dot { flex: 0 0 auto; width: .58rem; height: .58rem; margin-top: .42rem; border-radius: 999px; background: #cbd5e1; }
        .omc-mail__unread-dot.is-unread { background: var(--mail-accent); box-shadow: 0 0 0 4px rgba(48, 71, 232, .1); }
        .omc-mail__message-copy { min-width: 0; flex: 1; }
        .omc-mail__message-head { display: flex; align-items: baseline; justify-content: space-between; gap: .65rem; }
        .omc-mail__sender,
        .omc-mail__subject,
        .omc-mail__address { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .omc-mail__sender { color: var(--mail-text); font-size: .9rem; font-weight: 600; }
        .omc-mail__sender.is-unread { font-weight: 800; }
        .omc-mail__date { flex: 0 0 auto; color: var(--mail-muted); font-size: .72rem; }
        .omc-mail__subject { margin-top: .25rem; color: var(--mail-muted); font-size: .86rem; }
        .omc-mail__address { margin-top: .22rem; color: var(--mail-muted); font-size: .72rem; opacity: .8; }
        .omc-mail__star { width: 1.05rem; height: 1.05rem; color: #cbd5e1; }
        .omc-mail__star.is-active { color: #f59e0b; fill: currentColor; }

        .omc-mail__reader { min-height: 30rem; padding: 1.25rem; }
        .omc-mail__mobile-back { display: none; }
        .omc-mail__reader-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--mail-border); }
        .omc-mail__reader-copy { min-width: 0; }
        .omc-mail__reader h3 { overflow-wrap: anywhere; margin: 0; color: var(--mail-text); font-size: 1.2rem; font-weight: 800; line-height: 1.35; }
        .omc-mail__from { overflow-wrap: anywhere; margin: .55rem 0 0; color: var(--mail-muted); font-size: .86rem; }
        .omc-mail__reader-date { margin: .28rem 0 0; color: var(--mail-muted); font-size: .74rem; }
        .omc-mail__actions { display: flex; flex: 0 0 auto; gap: .35rem; }
        .omc-mail__body { overflow-wrap: anywhere; margin-top: 1.25rem; color: var(--mail-text); font-size: .9rem; line-height: 1.75; white-space: pre-wrap; }
        .omc-mail__html-frame { width: 100%; min-height: 60vh; margin-top: 1.25rem; border: 0; border-radius: .75rem; background: #fff; }
        .omc-mail__attachments { display: grid; gap: .6rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--mail-border); }
        .omc-mail__attachment {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: .7rem;
            padding: .72rem .85rem;
            border: 1px solid var(--mail-border);
            border-radius: .8rem;
            color: var(--mail-text);
            text-decoration: none;
            background: var(--mail-surface-subtle);
        }
        .omc-mail__attachment:hover { border-color: #818cf8; background: var(--mail-accent-soft); }
        .omc-mail__attachment-icon { width: 1.25rem; height: 1.25rem; flex: 0 0 auto; color: var(--mail-accent); }
        .omc-mail__attachment-copy { min-width: 0; flex: 1; }
        .omc-mail__attachment-name { display: block; overflow: hidden; text-overflow: ellipsis; color: var(--mail-text); font-size: .82rem; font-weight: 800; white-space: nowrap; }
        .omc-mail__attachment-meta { display: block; margin-top: .12rem; color: var(--mail-muted); font-size: .7rem; }
        .omc-mail__attachment-download { width: 1.05rem; height: 1.05rem; flex: 0 0 auto; color: var(--mail-muted); }
        .omc-mail__translation {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-top: 1rem;
            padding: .72rem .85rem;
            border: 1px solid rgba(48, 71, 232, .2);
            border-radius: .8rem;
            color: var(--mail-muted);
            background: var(--mail-accent-soft);
            font-size: .8rem;
        }
        .omc-mail__translation-copy { display: flex; min-width: 0; align-items: center; gap: .5rem; }
        .omc-mail__translation-icon { width: 1.05rem; height: 1.05rem; flex: 0 0 auto; color: var(--mail-accent); }
        .omc-mail__translation-button {
            flex: 0 0 auto;
            padding: .45rem .7rem;
            border: 0;
            border-radius: .62rem;
            color: #fff;
            background: var(--mail-accent);
            font-size: .76rem;
            font-weight: 800;
            cursor: pointer;
        }
        .omc-mail__translation-button.is-secondary { color: var(--mail-accent); background: var(--mail-surface); }
        .omc-mail__translation-button:disabled { cursor: wait; opacity: .65; }
        .omc-mail__links { display: grid; gap: .6rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--mail-border); }
        .omc-mail__links-title { margin: 0; color: var(--mail-muted); font-size: .76rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .omc-mail__link { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .75rem .9rem; border: 1px solid var(--mail-border); border-radius: .8rem; color: #4f46e5; font-size: .84rem; font-weight: 700; text-decoration: none; }
        .omc-mail__link:hover { border-color: #818cf8; background: rgba(99, 102, 241, .08); }
        .dark .omc-mail__link { color: #a5b4fc; }

        .omc-mail__empty {
            display: flex;
            min-height: 26rem;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: var(--mail-muted);
            text-align: center;
        }
        .omc-mail__empty--list { min-height: 14rem; }
        .omc-mail__empty-icon { width: 3rem; height: 3rem; margin-bottom: .75rem; color: #a5b4fc; }
        .omc-mail__empty-title { margin: 0; color: var(--mail-text); font-weight: 800; }
        .omc-mail__empty-text { max-width: 24rem; margin: .3rem 0 0; font-size: .84rem; line-height: 1.5; }

        .omc-mail__modal {
            position: fixed;
            z-index: 60;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 1rem;
            background: rgba(2, 6, 23, .72);
            backdrop-filter: blur(4px);
        }
        .omc-mail__composer { width: min(42rem, 100%); max-height: calc(100vh - 2rem); overflow-y: auto; padding: 1.25rem; border: 1px solid var(--mail-border); border-radius: 1.15rem; color: var(--mail-text); background: var(--mail-surface); box-shadow: 0 24px 70px rgba(0,0,0,.35); }
        .omc-mail__composer-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .omc-mail__composer h3 { margin: 0; color: var(--mail-text); font-size: 1.2rem; font-weight: 800; }
        .omc-mail__form { display: grid; gap: .9rem; margin-top: 1rem; }
        .omc-mail__label { display: grid; gap: .35rem; color: var(--mail-text); font-size: .84rem; font-weight: 700; }
        .omc-mail__textarea { min-height: 12rem; resize: vertical; }
        .omc-mail__validation { color: var(--mail-danger); font-size: .74rem; font-weight: 500; }
        .omc-mail__form-actions { display: flex; justify-content: flex-end; gap: .65rem; margin-top: .25rem; }

        @media (max-width: 1180px) {
            .omc-mail__layout { grid-template-columns: 12rem minmax(17rem, .85fr) minmax(0, 1.2fr); gap: .9rem; }
        }

        @media (max-width: 960px) {
            .omc-mail__layout { grid-template-columns: 1fr 1.25fr; }
            .omc-mail__nav { grid-column: 1 / -1; display: flex; gap: .25rem; overflow-x: auto; }
            .omc-mail__nav-title { display: none; }
            .omc-mail__folder { width: auto; flex: 0 0 auto; margin: 0; }
            .omc-mail__folder-label { min-width: max-content; }
            .omc-mail__list { max-height: 28rem; }
        }

        @media (max-width: 640px) {
            .omc-mail { gap: .9rem; }
            .omc-mail.has-open-message { gap: 0; }
            .omc-mail.has-open-message > .omc-mail__hero,
            .omc-mail.has-open-message > .omc-mail__push,
            .omc-mail.has-open-message .omc-mail__nav,
            .omc-mail.has-open-message .omc-mail__mailbox { display: none; }
            .omc-mail:not(.has-open-message) .omc-mail__reader { display: none; }
            .omc-mail__hero { align-items: stretch; flex-direction: column; padding: 1.1rem; border-radius: 1rem; }
            .omc-mail__hero-action > * { width: 100%; justify-content: center; }
            .omc-mail__layout { gap: .9rem; }
            .omc-mail.has-open-message .omc-mail__layout { display: block; }
            .omc-mail__layout { grid-template-columns: 1fr; }
            .omc-mail__nav { grid-column: auto; }
            .omc-mail__panel { border-radius: 1rem; }
            .omc-mail__reader { min-height: 70vh; padding: 1rem; }
            .omc-mail__html-frame { min-height: 72vh; }
            .omc-mail__mobile-back {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                margin: 0 0 1rem;
                padding: .55rem .7rem;
                border: 0;
                border-radius: .7rem;
                color: var(--mail-text);
                background: var(--mail-surface-subtle);
                font-size: .82rem;
                font-weight: 800;
                cursor: pointer;
            }
            .omc-mail__mobile-back-icon { width: 1.1rem; height: 1.1rem; }
            .omc-mail__reader-head { flex-direction: column; }
            .omc-mail__translation { align-items: stretch; flex-direction: column; }
            .omc-mail__translation-button { width: 100%; }
            .omc-mail__actions { width: 100%; justify-content: flex-end; }
            .omc-mail__empty { min-height: 18rem; }
            .omc-mail__form-actions { align-items: stretch; flex-direction: column-reverse; }
            .omc-mail__form-actions > * { width: 100%; justify-content: center; }
            .omc-mail__push { align-items: flex-start; flex-wrap: wrap; }
            .omc-mail__push-button { width: 100%; }
        }
    </style>

    <div @class(['omc-mail', 'has-open-message' => $selectedMessage])>
        <div class="omc-mail__hero">
            <div class="omc-mail__hero-copy">
                <p class="omc-mail__eyebrow">Захищена пошта ОМЦ</p>
                <h2>post@omc.pl.ua</h2>
                <p class="omc-mail__hero-description">Доступ контролює AdminPro. Пароль скриньки не передається браузеру або мобільному застосунку.</p>
            </div>
            <div class="omc-mail__hero-action">
                <x-filament::button color="gray" icon="heroicon-o-pencil-square" wire:click="startCompose">Новий лист</x-filament::button>
            </div>
        </div>

        @if ($error)
            <div class="omc-mail__error">{{ $error }}</div>
        @endif

        @php
            $firebaseWebConfig = [
                'apiKey' => config('services.firebase.web.api_key'),
                'authDomain' => config('services.firebase.web.auth_domain'),
                'projectId' => config('services.firebase.project_id'),
                'messagingSenderId' => config('services.firebase.web.messaging_sender_id'),
                'appId' => config('services.firebase.web.app_id'),
            ];
            $webPushConfigured = collect($firebaseWebConfig)->except('authDomain')->every(fn ($value) => filled($value))
                && filled(config('services.firebase.web.vapid_key'));
        @endphp
        <section class="omc-mail__push" data-admin-pro-mail-push
            data-configured="{{ $webPushConfigured ? 'true' : 'false' }}"
            data-firebase-config='@json($firebaseWebConfig)'
            data-vapid-key="{{ config('services.firebase.web.vapid_key') }}"
            data-register-url="{{ route('admin-pro.mail.push-device') }}">
            <x-filament::icon icon="heroicon-o-bell-alert" class="omc-mail__push-icon" />
            <div class="omc-mail__push-copy">
                <p class="omc-mail__push-title">Сповіщення про нові листи</p>
                <p class="omc-mail__push-status" data-push-status>Перевіряємо підтримку браузера…</p>
            </div>
            <button type="button" class="omc-mail__push-button" data-push-enable>Увімкнути сповіщення</button>
        </section>

        <div class="omc-mail__layout">
            <nav class="omc-mail__panel omc-mail__nav" aria-label="Поштові папки">
                <p class="omc-mail__nav-title">Папки</p>
                @foreach ($folders as $mailFolder)
                    @php
                        $folderIcon = match ($mailFolder['key']) {
                            'inbox' => 'heroicon-o-inbox',
                            'archive' => 'heroicon-o-archive-box',
                            'spam' => 'heroicon-o-shield-exclamation',
                            'trash' => 'heroicon-o-trash',
                            default => 'heroicon-o-folder',
                        };
                    @endphp
                    <button type="button" wire:click="selectFolder('{{ $mailFolder['key'] }}')"
                        @class(['omc-mail__folder', 'is-active' => $folder === $mailFolder['key']])>
                        <x-filament::icon :icon="$folderIcon" class="omc-mail__folder-icon" />
                        <span class="omc-mail__folder-label">{{ $mailFolder['label'] }}</span>
                        <span class="omc-mail__folder-count">{{ $mailFolder['key'] === 'inbox' && $mailFolder['unread'] > 0 ? $mailFolder['unread'] : $mailFolder['total'] }}</span>
                    </button>
                @endforeach
            </nav>

            <section class="omc-mail__panel omc-mail__mailbox">
                <div class="omc-mail__toolbar">
                    <input type="search" wire:model.live.debounce.400ms="search" placeholder="Пошук листів"
                        class="omc-mail__search" aria-label="Пошук листів" />
                    <x-filament::icon-button icon="heroicon-o-arrow-path" label="Оновити" wire:click="refreshMailbox" />
                </div>
                <div class="omc-mail__filters" aria-label="Фільтри листів">
                    @foreach (['all' => 'Усі', 'unread' => 'Непрочитані', 'starred' => 'Позначені'] as $filterKey => $filterLabel)
                        <button type="button" wire:click="selectFilter('{{ $filterKey }}')"
                            @class(['omc-mail__filter', 'is-active' => $filter === $filterKey])>{{ $filterLabel }}</button>
                    @endforeach
                </div>
                <div class="omc-mail__list">
                    @forelse ($messages as $message)
                        <button type="button" wire:key="admin-pro-mail-message-{{ $folder }}-{{ $message['uid'] }}"
                            wire:click.prevent="openMessage({{ $message['uid'] }})"
                            wire:loading.class="is-opening" wire:loading.attr="disabled"
                            wire:target="openMessage({{ $message['uid'] }})" class="omc-mail__message">
                            <span class="omc-mail__message-row">
                                <span @class(['omc-mail__unread-dot', 'is-unread' => ! $message['read']])></span>
                                <span class="omc-mail__message-copy">
                                    <span class="omc-mail__message-head">
                                        <span @class(['omc-mail__sender', 'is-unread' => ! $message['read']])>{{ $message['from_name'] }}</span>
                                        <span style="display:flex;align-items:center;gap:.35rem">
                                            <x-filament::icon icon="heroicon-s-star" @class(['omc-mail__star', 'is-active' => $message['flagged']]) />
                                            <span class="omc-mail__date">{{ $message['date'] ? \Illuminate\Support\Carbon::parse($message['date'])->format('d.m H:i') : '' }}</span>
                                        </span>
                                    </span>
                                    <span class="omc-mail__subject">{{ $message['subject'] }}</span>
                                    <span class="omc-mail__address">{{ $message['from_address'] }}</span>
                                </span>
                            </span>
                        </button>
                    @empty
                        <div class="omc-mail__empty omc-mail__empty--list">
                            <x-filament::icon icon="heroicon-o-inbox" class="omc-mail__empty-icon" />
                            <p class="omc-mail__empty-text">{{ $configured ? 'Листів немає.' : 'Скринька очікує серверного налаштування.' }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="omc-mail__panel omc-mail__reader">
                @if ($selectedMessage)
                    @php
                        $displayedMessage = $translatedMessage ?? $selectedMessage;
                        $renderedHtml = $translatedMessage ? null : ($selectedMessage['html'] ?? null);
                        $downloadableAttachments = collect($selectedMessage['attachments'] ?? [])->filter(
                            fn (array $attachment): bool => ! $attachment['inline'] || blank($attachment['content_id']),
                        );

                        if ($renderedHtml) {
                            foreach ($selectedMessage['attachments'] ?? [] as $attachment) {
                                if (blank($attachment['content_id'])) {
                                    continue;
                                }

                                $inlineUrl = route('admin-pro.mail.attachment', [
                                    'uid' => $selectedMessage['uid'],
                                    'part' => $attachment['part'],
                                    'folder' => $folder,
                                    'inline' => 1,
                                ]);
                                $renderedHtml = str_ireplace('cid:'.$attachment['content_id'], $inlineUrl, $renderedHtml);
                            }
                        }
                    @endphp
                    <button type="button" class="omc-mail__mobile-back" wire:click="closeMessage">
                        <x-filament::icon icon="heroicon-o-arrow-left" class="omc-mail__mobile-back-icon" />
                        <span>Назад до листів</span>
                    </button>
                    <div class="omc-mail__reader-head">
                        <div class="omc-mail__reader-copy">
                            <h3>{{ $displayedMessage['subject'] }}</h3>
                            <p class="omc-mail__from">{{ $selectedMessage['from_name'] }} &lt;{{ $selectedMessage['from_address'] }}&gt;</p>
                            <p class="omc-mail__reader-date">{{ $selectedMessage['date'] ? \Illuminate\Support\Carbon::parse($selectedMessage['date'])->format('d.m.Y H:i') : '' }}</p>
                        </div>
                        <div class="omc-mail__actions">
                            <x-filament::icon-button :icon="$selectedMessage['flagged'] ? 'heroicon-s-star' : 'heroicon-o-star'" color="warning"
                                :label="$selectedMessage['flagged'] ? 'Зняти позначку' : 'Позначити'"
                                wire:click="toggleFlag({{ $selectedMessage['uid'] }}, {{ $selectedMessage['flagged'] ? 'false' : 'true' }})" />
                            <x-filament::icon-button icon="heroicon-o-envelope" label="Позначити непрочитаним"
                                wire:click="markUnread({{ $selectedMessage['uid'] }})" />
                            <x-filament::icon-button icon="heroicon-o-arrow-uturn-left" label="Відповісти"
                                wire:click="startCompose(@js($selectedMessage['from_address']), @js($selectedMessage['subject']))" />
                            @if ($folder === 'trash')
                                <x-filament::icon-button icon="heroicon-o-arrow-up-tray" label="Відновити"
                                    wire:click="moveMessage({{ $selectedMessage['uid'] }}, 'inbox')" />
                            @else
                                @if ($folder !== 'archive')
                                    <x-filament::icon-button icon="heroicon-o-archive-box" label="Архівувати"
                                        wire:click="moveMessage({{ $selectedMessage['uid'] }}, 'archive')" />
                                @endif
                                @if ($folder !== 'spam')
                                    <x-filament::icon-button color="warning" icon="heroicon-o-shield-exclamation" label="У спам"
                                        wire:click="moveMessage({{ $selectedMessage['uid'] }}, 'spam')" />
                                @endif
                            @endif
                            <x-filament::icon-button color="danger" icon="heroicon-o-trash" label="Видалити"
                                wire:click="deleteMessage({{ $selectedMessage['uid'] }})"
                                wire:confirm="{{ $folder === 'trash' ? 'Видалити цей лист назавжди?' : 'Перемістити цей лист у видалені?' }}" />
                            <x-filament::icon-button color="gray" icon="heroicon-o-x-mark" label="Закрити" wire:click="closeMessage" />
                        </div>
                    </div>
                    @if ($translationSuggested || $translatedMessage)
                        <div class="omc-mail__translation" aria-live="polite">
                            <div class="omc-mail__translation-copy">
                                <x-filament::icon icon="heroicon-o-language" class="omc-mail__translation-icon" />
                                <span>{{ $translatedMessage ? 'Показано переклад українською' : 'Мова листа відрізняється від української' }}</span>
                            </div>
                            @if ($translatedMessage)
                                <button type="button" class="omc-mail__translation-button is-secondary" wire:click="showOriginalMessage">
                                    Показати оригінал
                                </button>
                            @else
                                <button type="button" class="omc-mail__translation-button" wire:click="translateSelectedMessage"
                                    wire:loading.attr="disabled" wire:target="translateSelectedMessage">
                                    <span wire:loading.remove wire:target="translateSelectedMessage">Перекласти українською</span>
                                    <span wire:loading wire:target="translateSelectedMessage">Перекладаємо…</span>
                                </button>
                            @endif
                        </div>
                    @endif
                    @if ($renderedHtml)
                        <iframe class="omc-mail__html-frame" title="Вміст листа" srcdoc="{{ $renderedHtml }}"
                            sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer"></iframe>
                    @else
                        <div class="omc-mail__body">{{ $displayedMessage['body'] }}</div>
                    @endif
                    @if ($downloadableAttachments->isNotEmpty())
                        <div class="omc-mail__attachments" aria-label="Вкладення листа">
                            <p class="omc-mail__links-title">Вкладення</p>
                            @foreach ($downloadableAttachments as $attachment)
                                @php
                                    $attachmentSize = $attachment['size'] > 0
                                        ? ($attachment['size'] >= 1048576
                                            ? number_format($attachment['size'] / 1048576, 1, ',', ' ').' МБ'
                                            : number_format(max(1, $attachment['size'] / 1024), 0, ',', ' ').' КБ')
                                        : 'Розмір не вказано';
                                @endphp
                                <a class="omc-mail__attachment" href="{{ route('admin-pro.mail.attachment', [
                                    'uid' => $selectedMessage['uid'],
                                    'part' => $attachment['part'],
                                    'folder' => $folder,
                                    'inline' => 1,
                                ]) }}" target="_blank" rel="noopener noreferrer">
                                    <x-filament::icon icon="heroicon-o-paper-clip" class="omc-mail__attachment-icon" />
                                    <span class="omc-mail__attachment-copy">
                                        <span class="omc-mail__attachment-name">{{ $attachment['name'] }}</span>
                                        <span class="omc-mail__attachment-meta">{{ $attachment['mime_type'] }} · {{ $attachmentSize }}</span>
                                    </span>
                                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="omc-mail__attachment-download" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if (! empty($selectedMessage['links']) && ! $renderedHtml)
                        <div class="omc-mail__links" aria-label="Посилання з листа">
                            <p class="omc-mail__links-title">Посилання з листа</p>
                            @foreach ($selectedMessage['links'] as $link)
                                <a class="omc-mail__link" href="{{ $link['url'] }}" target="_blank"
                                    rel="noopener noreferrer nofollow">
                                    <span>{{ $link['label'] }}</span>
                                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" style="width:1.1rem;height:1.1rem;flex:0 0 auto" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="omc-mail__empty">
                        <x-filament::icon icon="heroicon-o-envelope-open" class="omc-mail__empty-icon" />
                        <p class="omc-mail__empty-title">Оберіть лист</p>
                        <p class="omc-mail__empty-text">Текст листа відображається без виконання HTML і сторонніх скриптів.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

    @if ($composing)
        <div class="omc-mail__modal" role="dialog" aria-modal="true" aria-labelledby="omc-mail-compose-title">
            <div class="omc-mail__composer">
                <div class="omc-mail__composer-head">
                    <h3 id="omc-mail-compose-title">Новий лист</h3>
                    <x-filament::icon-button color="gray" icon="heroicon-o-x-mark" label="Закрити" wire:click="cancelCompose" />
                </div>
                <form wire:submit="sendMessage" class="omc-mail__form">
                    <label class="omc-mail__label"><span>Кому</span>
                        <input type="email" wire:model="to" autocomplete="email" class="omc-mail__field" />
                        @error('to') <span class="omc-mail__validation">{{ $message }}</span> @enderror
                    </label>
                    <label class="omc-mail__label"><span>Тема</span>
                        <input type="text" wire:model="subject" class="omc-mail__field" />
                        @error('subject') <span class="omc-mail__validation">{{ $message }}</span> @enderror
                    </label>
                    <label class="omc-mail__label"><span>Повідомлення</span>
                        <textarea wire:model="body" rows="10" class="omc-mail__textarea"></textarea>
                        @error('body') <span class="omc-mail__validation">{{ $message }}</span> @enderror
                    </label>
                    <div class="omc-mail__form-actions">
                        <x-filament::button type="button" color="gray" wire:click="cancelCompose">Скасувати</x-filament::button>
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">Надіслати</x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
