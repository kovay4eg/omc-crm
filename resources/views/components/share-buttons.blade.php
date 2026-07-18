@props([
    'url' => null,
    'title' => 'Обласний молодіжний центр',
    'description' => '',
])

@php
    $shareUrl = $url ?: url()->current();
    $facebookUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($shareUrl);
    $telegramUrl = 'https://t.me/share/url?url=' . rawurlencode($shareUrl) . '&text=' . rawurlencode($title);
@endphp

<style>
    .smm-share-panel {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        padding: 16px;
        border: 1px solid #d9dbff;
        border-radius: 16px;
        background: #f4f4ff;
    }

    .smm-share-label {
        width: 100%;
        margin: 0 0 2px;
        color: #242dc0;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .smm-share-button {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        padding: 9px 14px;
        border: 1px solid #3430d2;
        border-radius: 9px;
        background: #fff;
        color: #2924c8;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .smm-share-button:hover,
    .smm-share-button:focus-visible {
        background: #3430d2;
        color: #fff;
        transform: translateY(-2px);
        outline: none;
    }

    .smm-share-status {
        color: #3f4778;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 575px) {
        .smm-share-panel {
            gap: 8px;
        }

        .smm-share-button {
            flex: 1 1 calc(50% - 8px);
        }
    }
</style>

<div
    class="smm-share-panel"
    data-smm-share
    data-url="{{ $shareUrl }}"
    data-title="{{ $title }}"
    data-description="{{ $description }}"
>
    <p class="smm-share-label">Поділитися</p>

    <button type="button" class="smm-share-button" data-smm-native-share>
        ↗ Поділитися
    </button>

    <a
        class="smm-share-button"
        href="{{ $facebookUrl }}"
        target="_blank"
        rel="noopener noreferrer"
    >
        Facebook
    </a>

    <a
        class="smm-share-button"
        href="{{ $telegramUrl }}"
        target="_blank"
        rel="noopener noreferrer"
    >
        Telegram
    </a>

    <button type="button" class="smm-share-button" data-smm-app-share="instagram">
        Instagram
    </button>

    <button type="button" class="smm-share-button" data-smm-app-share="tiktok">
        TikTok
    </button>

    <button type="button" class="smm-share-button" data-smm-copy-link>
        Копіювати посилання
    </button>

    <span class="smm-share-status" data-smm-share-status aria-live="polite"></span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-smm-share]').forEach(panel => {
            if (panel.dataset.smmShareBound) {
                return;
            }

            panel.dataset.smmShareBound = 'true';

            const url = panel.dataset.url;
            const title = panel.dataset.title;
            const description = panel.dataset.description;
            const status = panel.querySelector('[data-smm-share-status]');

            const setStatus = message => {
                status.textContent = message;

                window.setTimeout(() => {
                    if (status.textContent === message) {
                        status.textContent = '';
                    }
                }, 3200);
            };

            const copyLink = async (message = 'Посилання скопійовано. Його можна вставити в Instagram або TikTok.') => {
                try {
                    await navigator.clipboard.writeText(url);
                    setStatus(message);
                } catch (error) {
                    const field = document.createElement('textarea');
                    field.value = url;
                    field.setAttribute('readonly', '');
                    field.style.position = 'fixed';
                    field.style.opacity = '0';
                    document.body.appendChild(field);
                    field.select();
                    document.execCommand('copy');
                    field.remove();
                    setStatus(message);
                }
            };

            panel.querySelector('[data-smm-copy-link]').addEventListener('click', copyLink);

            panel.querySelector('[data-smm-native-share]').addEventListener('click', async () => {
                if (!navigator.share) {
                    await copyLink();
                    return;
                }

                try {
                    await navigator.share({ title, text: description, url });
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        await copyLink();
                    }
                }
            });

            const socialApps = {
                instagram: {
                    url: 'https://www.instagram.com/',
                    name: 'Instagram',
                },
                tiktok: {
                    url: 'https://www.tiktok.com/',
                    name: 'TikTok',
                },
            };

            panel.querySelectorAll('[data-smm-app-share]').forEach(button => {
                button.addEventListener('click', async () => {
                    const app = socialApps[button.dataset.smmAppShare];

                    if (!app) {
                        return;
                    }

                    if (navigator.share) {
                        try {
                            await navigator.share({ title, text: description, url });
                            return;
                        } catch (error) {
                            if (error.name === 'AbortError') {
                                return;
                            }
                        }
                    }

                    window.open(app.url, '_blank', 'noopener,noreferrer');
                    await copyLink(`Посилання скопійовано. Вставте його у публікацію або сторіс ${app.name}.`);
                });
            });
        });
    });
</script>
