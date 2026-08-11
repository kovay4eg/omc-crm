<?php

namespace App\Filament\Pages;

use App\Services\AdminProMailboxService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class AdminProMail extends Page
{
    protected static ?string $navigationLabel = 'Пошта';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'AdminPro';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.admin-pro-mail';

    public array $messages = [];

    public ?array $selectedMessage = null;

    public string $search = '';

    public string $to = '';

    public string $subject = '';

    public string $body = '';

    public bool $composing = false;

    public bool $configured = false;

    public ?string $error = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdminPro() === true;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->refreshMailbox();
    }

    public function refreshMailbox(): void
    {
        $this->authorizeAdminPro();
        $this->error = null;

        try {
            $mailbox = app(AdminProMailboxService::class);
            $this->configured = $mailbox->configured();
            $this->messages = $mailbox->messages(search: $this->search)['data'];
        } catch (Throwable $exception) {
            report($exception);
            $this->messages = [];
            $this->error = $exception->getMessage();
        }
    }

    public function updatedSearch(): void
    {
        $this->refreshMailbox();
    }

    public function openMessage(int $uid): void
    {
        $this->authorizeAdminPro();

        try {
            $this->selectedMessage = app(AdminProMailboxService::class)->message($uid);
            $this->refreshMailbox();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    public function closeMessage(): void
    {
        $this->authorizeAdminPro();
        $this->selectedMessage = null;
    }

    public function startCompose(?string $recipient = null, ?string $replySubject = null): void
    {
        $this->authorizeAdminPro();
        $this->to = $recipient ?? '';
        $this->subject = $replySubject ? (str_starts_with($replySubject, 'Re:') ? $replySubject : 'Re: '.$replySubject) : '';
        $this->body = '';
        $this->composing = true;
    }

    public function cancelCompose(): void
    {
        $this->authorizeAdminPro();
        $this->reset(['to', 'subject', 'body', 'composing']);
    }

    public function sendMessage(): void
    {
        $this->authorizeAdminPro();
        $data = $this->validate([
            'to' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:50000'],
        ]);

        try {
            app(AdminProMailboxService::class)->send([$data['to']], $data['subject'], $data['body']);
            system_log('admin_pro_mail_send', 'AdminPro надіслав лист через post@omc.pl.ua.');
            $this->cancelCompose();
            Notification::make()->success()->title('Лист надіслано')->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title('Не вдалося надіслати лист')->body($exception->getMessage())->send();
        }
    }

    public function deleteMessage(int $uid): void
    {
        $this->authorizeAdminPro();

        try {
            app(AdminProMailboxService::class)->delete($uid);
            system_log('admin_pro_mail_delete', 'AdminPro видалив лист зі скриньки post@omc.pl.ua.');
            $this->selectedMessage = null;
            $this->refreshMailbox();
            Notification::make()->success()->title('Лист видалено')->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    private function authorizeAdminPro(): void
    {
        abort_unless(static::canAccess(), 403, 'Пошта доступна лише AdminPro.');
    }
}
