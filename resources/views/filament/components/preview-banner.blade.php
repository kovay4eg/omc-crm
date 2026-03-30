@php
    $user = auth()->user();
    $realRole = $user?->role;
    $previewRole = session('preview_role', $realRole);

    $roleLabels = [
        'admin' => 'Адмін',
        'editor' => 'Редактор',
        'content' => 'Контент-мейкер',
    ];
@endphp

@if($user && $realRole === 'admin' && $previewRole !== 'admin')
    <div style="
    background: rgba(245, 158, 11, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: black;
    padding: 8px 16px;
    text-align: center;
    font-weight: 600;
    z-index: 9999;
    border-bottom: 1px solid rgba(0,0,0,0.2);
">
        ⚠ Увага: увімкнено режим перегляду як:
        <strong>{{ $roleLabels[$previewRole] ?? $previewRole }}</strong>.
        Функції адміна недоступні.

        <form method="POST" action="{{ route('exit-preview') }}" style="display:inline;">
            @csrf
            <button type="submit" style="
                margin-left: 10px;
                background: black;
                color: white;
                padding: 4px 10px;
                border-radius: 6px;
                font-weight: 600;
            ">
                Вийти
            </button>
        </form>
    </div>
@endif