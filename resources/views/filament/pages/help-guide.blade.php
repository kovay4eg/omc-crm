<x-filament-panels::page>
    <style>
        .omc-guide {
            --guide-blue: #3a35d1;
            --guide-soft-blue: #edf0ff;
            --guide-green: #0e9f6e;
            --guide-yellow: #f59e0b;
            --guide-red: #e11d48;
            color: #e5e7eb;
        }

        .omc-guide * { box-sizing: border-box; }
        .omc-guide__hero {
            position: relative;
            overflow: hidden;
            padding: 2rem;
            border: 1px solid rgba(99, 102, 241, .35);
            border-radius: 1.5rem;
            background: linear-gradient(135deg, #252347 0%, #15152a 54%, #1d1b3a 100%);
        }
        .omc-guide__hero::after {
            position: absolute;
            width: 18rem;
            height: 18rem;
            right: -5rem;
            bottom: -10rem;
            content: '';
            background: rgba(96, 92, 255, .24);
            border-radius: 999px;
            filter: blur(10px);
        }
        .omc-guide__eyebrow { color: #c7d2fe; font-size: .75rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .omc-guide__hero h1 { max-width: 47rem; margin: .5rem 0 .75rem; color: #fff; font-size: clamp(1.7rem, 4vw, 2.6rem); font-weight: 800; line-height: 1.06; }
        .omc-guide__hero p { position: relative; z-index: 1; max-width: 46rem; margin: 0; color: #d1d5db; font-size: 1rem; line-height: 1.6; }
        .omc-guide__quick { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: .65rem; margin-top: 1.25rem; }
        .omc-guide__quick a { border: 1px solid rgba(255,255,255,.19); border-radius: 999px; padding: .5rem .8rem; color: #fff; font-size: .82rem; font-weight: 700; text-decoration: none; background: rgba(255,255,255,.08); transition: transform .2s ease, background .2s ease; }
        .omc-guide__quick a:hover { transform: translateY(-2px); background: rgba(255,255,255,.16); }
        .omc-guide__steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .9rem; margin: 1.25rem 0; }
        .omc-guide__step { display: flex; gap: .75rem; align-items: center; padding: 1rem; border: 1px solid #323244; border-radius: 1rem; background: #1c1c25; }
        .omc-guide__number { display: grid; flex: 0 0 auto; width: 2rem; height: 2rem; place-items: center; border-radius: .7rem; color: #fff; font-weight: 800; background: var(--guide-blue); }
        .omc-guide__step strong { display: block; color: #fff; font-size: .9rem; }
        .omc-guide__step span { color: #a1a1aa; font-size: .78rem; }
        .omc-guide__section { scroll-margin-top: 1rem; margin-top: 1.65rem; }
        .omc-guide__section-heading { display: flex; gap: .8rem; align-items: center; margin-bottom: .8rem; }
        .omc-guide__section-heading .guide-icon { display: grid; width: 2.4rem; height: 2.4rem; place-items: center; border-radius: .8rem; font-size: 1.2rem; background: #292851; }
        .omc-guide__section-heading h2 { margin: 0; color: #fff; font-size: 1.35rem; font-weight: 800; }
        .omc-guide__section-heading p { margin: .15rem 0 0; color: #a1a1aa; font-size: .88rem; }
        .omc-guide__grid { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(18rem, .7fr); gap: 1rem; }
        .omc-guide__card { overflow: hidden; border: 1px solid #30303d; border-radius: 1.1rem; background: #1b1b24; }
        .omc-guide__card-body { padding: 1.2rem; }
        .omc-guide__card h3 { margin: 0 0 .7rem; color: #fff; font-size: 1rem; font-weight: 800; }
        .omc-guide__list { display: grid; gap: .55rem; padding: 0; margin: 0; list-style: none; }
        .omc-guide__list li { position: relative; padding-left: 1.35rem; color: #d4d4d8; font-size: .9rem; line-height: 1.48; }
        .omc-guide__list li::before { position: absolute; top: .35rem; left: 0; width: .52rem; height: .52rem; content: ''; border-radius: 999px; background: #818cf8; }
        .omc-guide__hint { margin-top: .9rem; padding: .8rem .9rem; border-radius: .8rem; color: #dbeafe; font-size: .84rem; line-height: 1.45; background: rgba(59, 130, 246, .13); border: 1px solid rgba(96,165,250,.25); }
        .omc-guide__warning { margin-top: .9rem; padding: .8rem .9rem; border-radius: .8rem; color: #fef3c7; font-size: .84rem; line-height: 1.45; background: rgba(245, 158, 11, .11); border: 1px solid rgba(245,158,11,.3); }
        .guide-window { padding: .75rem; border-bottom: 1px solid #30303d; background: #12121a; }
        .guide-window__top { display: flex; gap: .3rem; align-items: center; margin-bottom: .75rem; }
        .guide-window__dot { width: .5rem; height: .5rem; border-radius: 50%; background: #fb7185; }
        .guide-window__dot:nth-child(2) { background: #fbbf24; }
        .guide-window__dot:nth-child(3) { background: #34d399; }
        .guide-window__title { margin-left: .35rem; color: #a1a1aa; font-size: .7rem; }
        .guide-field { height: 2.05rem; margin-top: .5rem; border: 1px solid #444456; border-radius: .45rem; padding: .5rem .6rem; color: #9ca3af; font-size: .72rem; background: #20202b; }
        .guide-field--long { height: 3.6rem; }
        .guide-file { display: grid; min-height: 4.5rem; margin-top: .5rem; place-items: center; border: 1px dashed #696987; border-radius: .55rem; color: #a5b4fc; font-size: .75rem; background: rgba(99,102,241,.07); }
        .guide-button { display: inline-block; margin-top: .65rem; border-radius: .45rem; padding: .47rem .75rem; color: #fff; font-size: .7rem; font-weight: 800; background: var(--guide-blue); }
        .guide-toggle { display: inline-flex; gap: .45rem; align-items: center; margin-top: .55rem; color: #d4d4d8; font-size: .72rem; }
        .guide-toggle::before { width: 1.8rem; height: 1rem; content: ''; border-radius: 999px; background: #4f46e5; box-shadow: inset 0 0 0 2px rgba(255,255,255,.12); }
        .guide-status { display: inline-flex; align-items: center; gap: .4rem; border-radius: 999px; padding: .3rem .55rem; font-size: .72rem; font-weight: 800; }
        .guide-status--draft { color: #fde68a; background: rgba(245,158,11,.13); }
        .guide-status--published { color: #86efac; background: rgba(34,197,94,.13); }
        .guide-status--cancelled { color: #fda4af; background: rgba(244,63,94,.14); }
        .guide-flow { display: grid; gap: .45rem; }
        .guide-flow__item { display: flex; gap: .65rem; align-items: flex-start; padding: .72rem; border-radius: .8rem; background: #242430; }
        .guide-flow__item b { display: grid; flex: 0 0 auto; width: 1.35rem; height: 1.35rem; place-items: center; border-radius: 50%; color: #fff; font-size: .7rem; background: #4f46e5; }
        .guide-flow__item span { color: #d4d4d8; font-size: .82rem; line-height: 1.4; }
        .omc-guide__choices { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: .75rem; margin-top: .95rem; }
        .guide-choice { padding: .9rem; border: 1px solid #353546; border-radius: .9rem; background: #20202a; }
        .guide-choice h4 { margin: 0 0 .35rem; color: #fff; font-size: .84rem; }
        .guide-choice p { margin: 0; color: #a1a1aa; font-size: .78rem; line-height: 1.45; }
        .guide-choice strong { color: #a5b4fc; }
        .guide-gallery { display: grid; grid-template-columns: repeat(3, 1fr); gap: .45rem; margin-top: .65rem; }
        .guide-gallery span { display: block; aspect-ratio: 1.35; border-radius: .5rem; background: linear-gradient(135deg, #6366f1, #c4b5fd); }
        .guide-gallery span:nth-child(2) { background: linear-gradient(135deg, #f59e0b, #fde68a); }
        .guide-gallery span:nth-child(3) { background: linear-gradient(135deg, #10b981, #a7f3d0); }
        .omc-guide__faq { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .75rem; }
        .omc-guide details { border: 1px solid #323244; border-radius: .85rem; padding: .85rem .95rem; background: #1b1b24; }
        .omc-guide summary { cursor: pointer; color: #fff; font-size: .88rem; font-weight: 800; }
        .omc-guide details p { margin: .65rem 0 0; color: #b4b4be; font-size: .84rem; line-height: 1.5; }
        @media (max-width: 980px) { .omc-guide__grid { grid-template-columns: 1fr; } }
        @media (max-width: 720px) { .omc-guide__hero { padding: 1.3rem; } .omc-guide__steps, .omc-guide__choices, .omc-guide__faq { grid-template-columns: 1fr; } }
    </style>

    <div class="omc-guide">
        <section class="omc-guide__hero">
            <div class="omc-guide__eyebrow">Довідка для команди ОМЦ</div>
            <h1>Як керувати сайтом — просто, крок за кроком</h1>
            <p>Тут лише щоденна робота з контентом. Відкрий потрібний розділ у меню, внеси зміни, натисни <b>«Зберегти»</b> — і перевір результат на сайті.</p>
            <nav class="omc-guide__quick" aria-label="Розділи довідки">
                <a href="#home">Головна сторінка</a>
                <a href="#events">Івенти</a>
                <a href="#summaries">Підсумки</a>
                <a href="#smm">SMM і соцмережі</a>
                <a href="#team">Працівники</a>
                <a href="#documents">Документи</a>
                <a href="#help">Якщо щось не так</a>
            </nav>
        </section>

        <section class="omc-guide__steps" aria-label="Основний порядок роботи">
            <div class="omc-guide__step"><b class="omc-guide__number">1</b><div><strong>Відкрий розділ</strong><span>у меню ліворуч</span></div></div>
            <div class="omc-guide__step"><b class="omc-guide__number">2</b><div><strong>Додай або зміни дані</strong><span>поля з * — обов’язкові</span></div></div>
            <div class="omc-guide__step"><b class="omc-guide__number">3</b><div><strong>Збережи й перевір</strong><span>подивись результат на сайті</span></div></div>
        </section>

        <section id="home" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">🏠</span><div><h2>Головна сторінка та контакти</h2><p>Меню: «Головна сторінка» і «Футер».</p></div></div>
            <div class="omc-guide__grid">
                <article class="omc-guide__card"><div class="omc-guide__card-body">
                    <h3>Що тут можна змінити</h3>
                    <ul class="omc-guide__list">
                        <li><b>Банер</b> — велике фото на першому екрані. Для телефону додай окремий вертикальний банер.</li>
                        <li><b>Логотип</b> — використовується в шапці сайту та адміністративній панелі.</li>
                        <li><b>Адреса, телефон, email і Google Maps</b> — відображаються в «Контактах» і футері.</li>
                        <li><b>Соцмережі</b> — увімкни перемикач, встав повне посилання й збережи. Вимкнений перемикач приховає іконку на сайті.</li>
                        <li><b>Футер</b> — додай до 5 логотипів партнерів. Перетягуванням можна змінити їхній порядок.</li>
                    </ul>
                    <div class="omc-guide__hint">💡 Посилання завжди вставляй повністю, наприклад: <b>https://instagram.com/...</b></div>
                </div></article>
                <aside class="omc-guide__card"><div class="guide-window"><div class="guide-window__top"><i class="guide-window__dot"></i><i class="guide-window__dot"></i><i class="guide-window__dot"></i><span class="guide-window__title">Головна сторінка</span></div><div class="guide-file">🖼️ Натисни або перетягни банер</div><div class="guide-field">м. Полтава, адреса</div><div class="guide-field">+380 …</div><span class="guide-toggle">Показувати Instagram</span><span class="guide-button">ЗБЕРЕГТИ</span></div></aside>
            </div>
        </section>

        <section id="events" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">📅</span><div><h2>Івенти: анонс заходу</h2><p>Меню: «Івенти» → «Створити».</p></div></div>
            <div class="omc-guide__grid">
                <article class="omc-guide__card"><div class="omc-guide__card-body">
                    <h3>Створи анонс у такому порядку</h3>
                    <div class="guide-flow">
                        <div class="guide-flow__item"><b>1</b><span>Напиши коротку <strong>назву</strong>, зрозумілий опис, дату й час. Додай афішу або фото заходу.</span></div>
                        <div class="guide-flow__item"><b>2</b><span>За потреби увімкни <strong>«Показувати кнопку “Записатись”»</strong> і вибери тип реєстрації нижче.</span></div>
                        <div class="guide-flow__item"><b>3</b><span>Обери стан: <strong>«Чернетка»</strong> для перевірки або <strong>«Опубліковано»</strong>, щоб показати захід на сайті.</span></div>
                    </div>
                    <div class="omc-guide__choices">
                        <div class="guide-choice"><h4>Без реєстрації</h4><p>Залиш кнопку вимкненою. На картці буде лише інформація про захід.</p></div>
                        <div class="guide-choice"><h4>Форма сайту</h4><p>Вибери <strong>«Форма (ім’я, телефон, email)»</strong>. Заявки потрапляють у вкладку «Реєстрації» цього івенту.</p></div>
                        <div class="guide-choice"><h4>Google форма</h4><p>Встав повне посилання на форму. Кнопка відкриє Google форму; кількість заявок на сайті не показується.</p></div>
                    </div>
                    <div class="omc-guide__warning">⚠️ Захід з датою в минулому автоматично зникає з анонсів наступного дня. Якщо він уже відбувся — створи для нього підсумок.</div>
                </div></article>
                <aside class="omc-guide__card"><div class="guide-window"><div class="guide-window__top"><i class="guide-window__dot"></i><i class="guide-window__dot"></i><i class="guide-window__dot"></i><span class="guide-window__title">Створити івент</span></div><div class="guide-field">Назва заходу *</div><div class="guide-field guide-field--long">Опис заходу *</div><div class="guide-file">🖼️ Фото / афіша</div><span class="guide-toggle">Показувати кнопку «Записатись»</span><div class="guide-field">Статус: Опубліковано</div><span class="guide-button">СТВОРИТИ</span></div></aside>
            </div>
            <div class="omc-guide__grid" style="margin-top:1rem">
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Як змінити дату або скасувати захід</h3><ul class="omc-guide__list"><li>Відкрий потрібний захід зі списку.</li><li>Угорі натисни <b>«Перенести івент»</b> або <b>«Скасувати івент»</b>.</li><li>Обов’язково вкажи причину. Увімкни показ причини на сайті, лише якщо вона має бути видимою відвідувачам.</li><li>Після перенесення захід автоматично стане на нове місце за датою. Скасований захід залишиться на своїй даті, але буде сірим і перекресленим.</li></ul></div></article>
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Реєстрації на захід</h3><ul class="omc-guide__list"><li>Відкрий івент → вкладка <b>«Реєстрації»</b>.</li><li>Там видно заявки з форми сайту; за потреби їх можна додати вручну.</li><li>Вільні місця рахуються саме за записами в цій таблиці. Коли місць немає, кнопка запису на сайті стає недоступною.</li></ul><div class="omc-guide__hint">Для Google форми учасники з’являться в цій системі лише після майбутнього підключення імпорту — це нормально.</div></div></article>
            </div>
        </section>

        <section id="summaries" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">📝</span><div><h2>Підсумки заходів</h2><p>Меню: «Підсумки заходів». Тут лише заходи, що вже відбулися.</p></div></div>
            <div class="omc-guide__grid">
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Що робити після заходу</h3><ul class="omc-guide__list"><li>Знайди захід у списку та відкрий його.</li><li>Напиши <b>текст підсумку</b>: що відбулося, хто взяв участь, результати або головні враження.</li><li>Додай одразу кілька фото у блоці «Фотогалерея». Це поле необов’язкове.</li><li>Спочатку обери <span class="guide-status guide-status--draft">Чернетка</span> — її бачить лише команда. Кнопка «Попередній перегляд» допоможе перевірити сторінку.</li><li>Коли все готово, зміни стан на <span class="guide-status guide-status--published">Опубліковано</span> і збережи. Тоді підсумок з’явиться на сайті.</li></ul><div class="omc-guide__hint">Історія створення, редагування та публікації зберігається автоматично разом з автором зміни.</div></div></article>
                <aside class="omc-guide__card"><div class="guide-window"><div class="guide-window__top"><i class="guide-window__dot"></i><i class="guide-window__dot"></i><i class="guide-window__dot"></i><span class="guide-window__title">Підсумок заходу</span></div><div class="guide-field guide-field--long">Текст підсумку *</div><div class="guide-field">Стан: Чернетка</div><div class="guide-gallery"><span></span><span></span><span></span></div><span class="guide-button">ЗБЕРЕГТИ</span></div></aside>
            </div>
        </section>

        <section id="smm" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">📣</span><div><h2>SMM і поширення матеріалів</h2><p>Як підготувати анонс або підсумок для Facebook, Telegram, Instagram і TikTok.</p></div></div>
            <div class="omc-guide__grid">
                <article class="omc-guide__card"><div class="omc-guide__card-body">
                    <h3>Підготуй матеріал перед публікацією</h3>
                    <div class="guide-flow">
                        <div class="guide-flow__item"><b>1</b><span>У «Головній сторінці», івенті або підсумку знайди блок <strong>«SMM і прев’ю посилань»</strong>.</span></div>
                        <div class="guide-flow__item"><b>2</b><span>За бажанням заповни окремий <strong>заголовок</strong>, короткий опис і завантаж обкладинку <strong>1200 × 630 px</strong>. Якщо залишити поля порожніми, сайт візьме назву, опис і фото матеріалу автоматично.</span></div>
                        <div class="guide-flow__item"><b>3</b><span>Збережи. В івенті або підсумку натисни <strong>«Відкрити SMM-сторінку»</strong>, щоб побачити точне посилання для публікації.</span></div>
                    </div>
                    <div class="omc-guide__hint">💡 Для Facebook і Telegram найкраще працює горизонтальна обкладинка 1200 × 630 px з великим читабельним текстом.</div>
                    <div class="omc-guide__warning">⚠️ Не використовуй на SMM-обкладинці дрібний текст, важливі деталі біля країв або фото низької якості.</div>
                </div></article>
                <aside class="omc-guide__card"><div class="guide-window"><div class="guide-window__top"><i class="guide-window__dot"></i><i class="guide-window__dot"></i><i class="guide-window__dot"></i><span class="guide-window__title">SMM і прев’ю посилань</span></div><div class="guide-field">Заголовок для соцмереж</div><div class="guide-field guide-field--long">Короткий опис до 200 символів</div><div class="guide-file">🖼️ Обкладинка 1200 × 630 px</div><span class="guide-button">ЗБЕРЕГТИ</span></div></aside>
            </div>
            <div class="omc-guide__grid" style="margin-top:1rem">
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Як поширити</h3><ul class="omc-guide__list"><li>На сторінці анонсу або підсумку внизу є панель <b>«Поділитися»</b>.</li><li><b>Facebook</b> і <b>Telegram</b> відкривають вікно публікації одразу.</li><li>На телефоні кнопки <b>Instagram</b> і <b>TikTok</b> відкривають системне меню — обери потрібний застосунок.</li><li>На комп’ютері посилання копіюється, а Instagram або TikTok відкривається у новій вкладці. Встав посилання вручну в публікацію, профіль або сторіс.</li><li>Перед публікацією відкрий посилання в режимі інкогніто: так можна побачити, яке прев’ю отримає відвідувач.</li></ul></div></article>
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Важливо знати</h3><ul class="omc-guide__list"><li>Instagram і TikTok не дозволяють сайту автоматично створити пост або сторіс без офіційного підключення бізнес-акаунтів.</li><li>Після зміни обкладинки соцмережа може ще деякий час показувати старе прев’ю — це кеш самої соцмережі, не помилка сайту.</li><li>Для сторіс використовуй окреме вертикальне зображення 1080 × 1920 px, а посилання вставляй через наліпку «Посилання» у застосунку.</li></ul><div class="omc-guide__hint">Короткий порядок: <b>підготував → відкрив SMM-сторінку → перевірив прев’ю → поширив</b>.</div></div></article>
            </div>
        </section>

        <section id="team" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">👥</span><div><h2>Працівники хабу</h2><p>Меню: «Працівники хабу» → «Створити».</p></div></div>
            <div class="omc-guide__grid">
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Додавання працівника</h3><ul class="omc-guide__list"><li>Заповни ім’я, прізвище та, за потреби, по батькові.</li><li>Обери посаду й відділ зі списків.</li><li>Додай портретне фото. У редакторі обріж його у пропорції <b>3:4</b> — так фото виглядатиме акуратно на сайті.</li><li>Збережи зміни. Для коректного вигляду обирай чітке вертикальне фото людини.</li></ul></div></article>
                <aside class="omc-guide__card"><div class="guide-window"><div class="guide-window__top"><i class="guide-window__dot"></i><i class="guide-window__dot"></i><i class="guide-window__dot"></i><span class="guide-window__title">Працівник</span></div><div class="guide-file">👤 Фото 3 : 4</div><div class="guide-field">Ім’я *</div><div class="guide-field">Посада *</div><span class="guide-button">ЗБЕРЕГТИ</span></div></aside>
            </div>
        </section>

        <section id="documents" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">📂</span><div><h2>Документи на сайті</h2><p>Меню: «Звітність», «Календарний план», «Статут».</p></div></div>
            <div class="omc-guide__grid">
                <article class="omc-guide__card"><div class="omc-guide__card-body"><h3>Просте правило для всіх документів</h3><ul class="omc-guide__list"><li>Натисни <b>«Створити»</b>, напиши зрозумілу назву та завантаж фінальний файл.</li><li>Для <b>звітності</b> додай рік; підтримуються PDF, Word та Excel.</li><li>Для <b>календарного плану</b> заповни назву та рік; підтримуються PDF, Word та Excel.</li><li>Для <b>статуту</b> додай назву й файл PDF або Word.</li><li>Перед завантаженням перейменуй файл зрозуміло: <b>zvit-2026.pdf</b>, а не <b>final_new_2.pdf</b>.</li></ul><div class="omc-guide__hint">Після збереження документ одразу можна відкрити або завантажити зі списку в адмінці.</div></div></article>
                <aside class="omc-guide__card"><div class="guide-window"><div class="guide-window__top"><i class="guide-window__dot"></i><i class="guide-window__dot"></i><i class="guide-window__dot"></i><span class="guide-window__title">Новий документ</span></div><div class="guide-field">Назва документа *</div><div class="guide-field">Рік: 2026 *</div><div class="guide-file">📄 PDF / DOCX / XLSX</div><span class="guide-button">СТВОРИТИ</span></div></aside>
            </div>
        </section>

        <section id="help" class="omc-guide__section">
            <div class="omc-guide__section-heading"><span class="guide-icon">💡</span><div><h2>Якщо щось не так</h2><p>Спочатку перевір ці прості причини.</p></div></div>
            <div class="omc-guide__faq">
                <details><summary>Івент не видно на сайті</summary><p>Перевір, що обрано «Опубліковано», дата заходу ще не минула, а сторінку сайту оновлено. Чернетки відвідувачі не бачать.</p></details>
                <details><summary>Не видно підсумок заходу</summary><p>Перевір стан підсумку: має бути «Опубліковано». Чернетка доступна лише в адмінці та через попередній перегляд.</p></details>
                <details><summary>Іконка соцмережі не з’явилась</summary><p>У «Головній сторінці» увімкни перемикач цієї мережі, встав посилання з https:// та натисни «Зберегти».</p></details>
                <details><summary>Фото виглядає розтягнутим</summary><p>Завантаж інше фото або обріж його у вбудованому редакторі. Для працівників використовуй вертикальне фото 3:4.</p></details>
            </div>
        </section>
    </div>
</x-filament-panels::page>
