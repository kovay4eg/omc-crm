<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 p-6 text-white shadow-lg sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-white/70">Приватна скринька AdminPro</p>
                <h2 class="mt-1 text-2xl font-bold">post@omc.pl.ua</h2>
                <p class="mt-2 max-w-2xl text-sm text-white/80">Листи доступні лише поточному власнику AdminPro. Пароль скриньки не передається браузеру.</p>
            </div>
            <x-filament::button color="gray" icon="heroicon-o-pencil-square" wire:click="startCompose">Новий лист</x-filament::button>
        </div>

        @if ($error)
            <div class="rounded-xl border border-danger-300 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-700 dark:bg-danger-950 dark:text-danger-200">{{ $error }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(300px,0.9fr)_minmax(0,1.5fr)]">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center gap-3 border-b border-gray-200 p-4 dark:border-white/10">
                    <input type="search" wire:model.live.debounce.400ms="search" placeholder="Пошук листів"
                        class="w-full rounded-xl border-gray-300 bg-transparent text-sm dark:border-white/10" />
                    <x-filament::icon-button icon="heroicon-o-arrow-path" label="Оновити" wire:click="refreshMailbox" />
                </div>
                <div class="max-h-[65vh] overflow-y-auto">
                    @forelse ($messages as $message)
                        <button type="button" wire:click="openMessage({{ $message['uid'] }})"
                            class="block w-full border-b border-gray-100 p-4 text-left transition hover:bg-primary-50 dark:border-white/5 dark:hover:bg-white/5">
                            <span class="flex items-start gap-3">
                                <span @class(['mt-2 h-2.5 w-2.5 shrink-0 rounded-full', 'bg-primary-500' => ! $message['read'], 'bg-gray-300 dark:bg-gray-700' => $message['read']])></span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span @class(['truncate text-sm', 'font-bold' => ! $message['read'], 'font-medium' => $message['read']])>{{ $message['from_name'] }}</span>
                                        <span class="shrink-0 text-xs text-gray-500">{{ $message['date'] ? \Illuminate\Support\Carbon::parse($message['date'])->format('d.m H:i') : '' }}</span>
                                    </span>
                                    <span class="mt-1 block truncate text-sm text-gray-600 dark:text-gray-300">{{ $message['subject'] }}</span>
                                    <span class="mt-1 block truncate text-xs text-gray-400">{{ $message['from_address'] }}</span>
                                </span>
                            </span>
                        </button>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-500">
                            <x-filament::icon icon="heroicon-o-inbox" class="mx-auto mb-3 h-10 w-10 text-gray-300" />
                            {{ $configured ? 'Листів немає.' : 'Скринька очікує серверного налаштування.' }}
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="min-h-[420px] rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                @if ($selectedMessage)
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-200 pb-5 dark:border-white/10">
                        <div class="min-w-0">
                            <h3 class="text-xl font-bold text-gray-950 dark:text-white">{{ $selectedMessage['subject'] }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $selectedMessage['from_name'] }} &lt;{{ $selectedMessage['from_address'] }}&gt;</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $selectedMessage['date'] ? \Illuminate\Support\Carbon::parse($selectedMessage['date'])->format('d.m.Y H:i') : '' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <x-filament::icon-button icon="heroicon-o-arrow-uturn-left" label="Відповісти"
                                wire:click="startCompose(@js($selectedMessage['from_address']), @js($selectedMessage['subject']))" />
                            <x-filament::icon-button color="danger" icon="heroicon-o-trash" label="Видалити"
                                wire:click="deleteMessage({{ $selectedMessage['uid'] }})" wire:confirm="Видалити цей лист?" />
                            <x-filament::icon-button color="gray" icon="heroicon-o-x-mark" label="Закрити" wire:click="closeMessage" />
                        </div>
                    </div>
                    <div class="mt-6 whitespace-pre-wrap break-words text-sm leading-7 text-gray-800 dark:text-gray-200">{{ $selectedMessage['body'] }}</div>
                @else
                    <div class="flex min-h-[380px] flex-col items-center justify-center text-center text-gray-500">
                        <x-filament::icon icon="heroicon-o-envelope-open" class="mb-4 h-14 w-14 text-primary-300" />
                        <p class="font-semibold text-gray-700 dark:text-gray-200">Оберіть лист</p>
                        <p class="mt-1 max-w-sm text-sm">Текст листа відображається без виконання HTML і сторонніх скриптів.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

    @if ($composing)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-950 dark:text-white">Новий лист</h3>
                    <x-filament::icon-button color="gray" icon="heroicon-o-x-mark" label="Закрити" wire:click="cancelCompose" />
                </div>
                <form wire:submit="sendMessage" class="mt-5 space-y-4">
                    <label class="block"><span class="mb-1 block text-sm font-medium">Кому</span>
                        <input type="email" wire:model="to" autocomplete="email" class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-950" />
                        @error('to') <span class="mt-1 block text-xs text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block"><span class="mb-1 block text-sm font-medium">Тема</span>
                        <input type="text" wire:model="subject" class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-950" />
                        @error('subject') <span class="mt-1 block text-xs text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block"><span class="mb-1 block text-sm font-medium">Повідомлення</span>
                        <textarea wire:model="body" rows="10" class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-950"></textarea>
                        @error('body') <span class="mt-1 block text-xs text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <div class="flex justify-end gap-3">
                        <x-filament::button type="button" color="gray" wire:click="cancelCompose">Скасувати</x-filament::button>
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">Надіслати</x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
