<x-filament::widget>
    <x-filament::card>

        <div class="mb-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                📅 Календар івентів
            </h2>
            <p class="text-sm text-gray-400">Перегляд по годинах</p>
        </div>

        @php
            $events = \App\Models\Event::with(['user','registrations'])->get()->map(function ($event) {

                $start = \Carbon\Carbon::parse($event->event_date);

                $participants = $event->registrations->count();
                $max = $event->max_participants;

                // 🔥 КОЛІР (НЕ залежить від кнопки)
                if ($max && $participants >= $max) {
                    $color = '#ef4444'; // повний
                    $status = 'full';
                } elseif ($max) {
                    $color = '#f59e0b'; // обмежений
                    $status = 'limited';
                } else {
                    $color = '#22c55e'; // безліміт
                    $status = 'unlimited';
                }

                // 🔥 ТЕКСТ СТАТУСУ
                if (!$event->has_registration_button) {
                    $statusText = 'Реєстрація вимкнена';
                } elseif ($status === 'full') {
                    $statusText = 'Немає місць';
                } elseif ($status === 'limited') {
                    $statusText = 'Залишилось: ' . ($max - $participants);
                } else {
                    $statusText = 'Без обмежень';
                }

                return [
                    'title' => $event->title,
                    'start' => $start->toIso8601String(),
                    'end' => $start->copy()->addHour()->toIso8601String(),

                    'backgroundColor' => $color,
                    'borderColor' => 'transparent',

                    'extendedProps' => [
                        'author' => $event->user->name ?? 'Невідомо',
                        'participants' => $participants,
                        'max' => $max,
                        'status_text' => $statusText,
                        'registration_enabled' => $event->has_registration_button,
                    ],
                ];
            })->values();
        @endphp

        <!-- CALENDAR -->
        <div
            x-init="
                setTimeout(() => {

                    const calendarEl = $el.querySelector('#calendar');
                    const eventsData = {{ json_encode($events) }};

                    const calendar = new FullCalendar.Calendar(calendarEl, {

                        locale: 'uk',
                        initialView: 'timeGridWeek',
                        height: 'auto',

                        slotMinTime: '08:00:00',
                        slotMaxTime: '22:00:00',

                        nowIndicator: true,
                        eventOverlap: false,
                        slotEventOverlap: false,

                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'timeGridWeek,dayGridMonth'
                        },

                        buttonText: {
                            today: 'Сьогодні',
                            timeGridWeek: 'Тиждень',
                            dayGridMonth: 'Місяць'
                        },

                        events: eventsData,

                        eventClick: function(info) {

                            const e = info.event;
                            const start = new Date(e.start);

                            document.getElementById('popupTitle').innerText = e.title;
                            document.getElementById('popupDate').innerText =
                                start.toLocaleDateString('uk-UA');

                            document.getElementById('popupTime').innerText =
                                start.toLocaleTimeString('uk-UA',{hour:'2-digit',minute:'2-digit'});

                            document.getElementById('popupAuthor').innerText =
                                e.extendedProps.author;

                            document.getElementById('popupStatus').innerText =
                                e.extendedProps.status_text;

                            const participantsBlock = document.getElementById('participantsBlock');

                            if (e.extendedProps.registration_enabled) {
                                participantsBlock.style.display = 'block';

                                document.getElementById('popupParticipants').innerText =
                                    e.extendedProps.participants;

                                document.getElementById('popupMax').innerText =
                                    e.extendedProps.max ?? '∞';
                            } else {
                                participantsBlock.style.display = 'none';
                            }

                            document.getElementById('simplePopup').style.display = 'flex';
                        }

                    });

                    calendar.render();

                }, 200);
            "
        >
            <div id="calendar"></div>
        </div>

        <!-- POPUP -->
        <div id="simplePopup" style="
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index:9999;
            align-items:center;
            justify-content:center;
        ">

            <div style="
                background:#111827;
                border-radius:16px;
                padding:20px;
                width:400px;
                max-width:90%;
                box-shadow:0 20px 50px rgba(0,0,0,0.5);
                color:white;
                animation: popupFade 0.2s ease;
            ">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <div id="popupTitle" style="font-size:18px;font-weight:bold;"></div>

                    <button onclick="document.getElementById('simplePopup').style.display='none'"
                        style="background:#1f2937;border:none;color:#aaa;border-radius:8px;width:30px;height:30px;cursor:pointer;">
                        ✕
                    </button>
                </div>

                <div style="font-size:14px;color:#ccc;line-height:1.8;">
                    📅 <b>Дата:</b> <span id="popupDate"></span><br>
                    🕒 <b>Час:</b> <span id="popupTime"></span><br>
                    👤 <b>Автор:</b> <span id="popupAuthor"></span><br>

                    <div id="participantsBlock">
                        👥 <b>Учасники:</b> 
                        <span id="popupParticipants"></span> / 
                        <span id="popupMax"></span><br>
                    </div>

                    📊 <b>Статус:</b> <span id="popupStatus"></span>
                </div>

                <div style="margin-top:15px;text-align:right;">
                    <button onclick="document.getElementById('simplePopup').style.display='none'"
                        style="background:#374151;border:none;padding:8px 14px;border-radius:10px;color:white;cursor:pointer;">
                        Закрити
                    </button>
                </div>

            </div>
        </div>

        <style>
            .fc { color: white; }

            .fc-theme-standard td,
            .fc-theme-standard th {
                border-color: #1f2937;
            }

            .fc-col-header-cell {
                background: #111827;
                color: #9ca3af;
            }

            .fc-day-today {
                background: rgba(59,130,246,0.1)!important;
            }

            .fc-event {
                border-radius: 10px !important;
                padding: 6px 8px !important;
                font-size: 12px;
            }

            .fc-event:hover {
                transform: scale(1.05);
                transition: 0.2s;
            }

            .fc-button {
                background: #1f2937 !important;
                border-radius: 10px !important;
                border: 1px solid #374151 !important;
            }

            @keyframes popupFade {
                from { opacity:0; transform:scale(0.9); }
                to { opacity:1; transform:scale(1); }
            }
        </style>

    </x-filament::card>
</x-filament::widget>