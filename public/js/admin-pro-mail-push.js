(function () {
    async function setupMailPush() {
        const root = document.querySelector('[data-admin-pro-mail-push]');
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';

        const button = root.querySelector('[data-push-enable]');
        const status = root.querySelector('[data-push-status]');
        const configured = root.dataset.configured === 'true';
        const setStatus = (message, state) => {
            status.textContent = message;
            root.dataset.state = state || '';
        };

        if (!configured) {
            setStatus('Веб-сповіщення очікують налаштування Firebase.', 'unavailable');
            button.hidden = true;
            return;
        }
        if (!('Notification' in window) || !('serviceWorker' in navigator) || !window.firebase) {
            setStatus('Цей браузер не підтримує push-сповіщення.', 'unavailable');
            button.hidden = true;
            return;
        }

        const config = JSON.parse(root.dataset.firebaseConfig);
        const registerToken = async () => {
            setStatus('Підключаємо захищені сповіщення…', 'loading');
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', { scope: '/' });
            const app = firebase.apps.length ? firebase.app() : firebase.initializeApp(config);
            const messaging = firebase.messaging(app);
            const token = await messaging.getToken({
                vapidKey: root.dataset.vapidKey,
                serviceWorkerRegistration: registration,
            });
            if (!token) throw new Error('Браузер не повернув push-токен.');

            const response = await fetch(root.dataset.registerUrl, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ token }),
            });
            if (!response.ok) throw new Error('Сервер не прийняв push-токен.');

            messaging.onMessage((payload) => {
                const notification = payload.notification || {};
                if (Notification.permission === 'granted' && document.visibilityState === 'visible') {
                    new Notification(notification.title || 'Пошта ОМЦ', {
                        body: notification.body || 'Отримано новий лист.',
                        icon: '/images/omc-logo.png',
                    });
                }
            });
            setStatus('Сповіщення про нові листи увімкнено.', 'enabled');
            button.hidden = true;
        };

        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    setStatus('Дозвіл не надано. Його можна змінити в налаштуваннях браузера.', 'denied');
                    return;
                }
                await registerToken();
            } catch (error) {
                console.error('Mail push registration failed', error);
                setStatus('Не вдалося увімкнути сповіщення. Спробуйте ще раз.', 'error');
            } finally {
                button.disabled = false;
            }
        });

        if (Notification.permission === 'granted') {
            try {
                await registerToken();
            } catch (error) {
                console.error('Mail push refresh failed', error);
                setStatus('Натисніть, щоб повторно підключити сповіщення.', 'error');
                button.hidden = false;
            }
        } else if (Notification.permission === 'denied') {
            setStatus('Сповіщення заблоковані в налаштуваннях браузера.', 'denied');
            button.hidden = true;
        } else {
            setStatus('Дозвольте сповіщення, щоб не пропускати нові листи.', 'prompt');
        }
    }

    document.addEventListener('DOMContentLoaded', setupMailPush);
    document.addEventListener('livewire:navigated', setupMailPush);
    setupMailPush();
})();
