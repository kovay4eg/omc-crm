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

    public array $folders = [];

    public ?array $selectedMessage = null;

    public string $search = '';

    public string $folder = 'inbox';

    public string $filter = 'all';

    public string $to = '';

    public string $subject = '';

    public string $body = '';

    public bool $composing = false;

    public bool $configured = false;

    public ?string $error = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->canAccessAdminProMail() === true;
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
            if (! $this->configured) {
                $this->folders = [];
                $this->messages = [];

                return;
            }

            $this->folders = $mailbox->folders();
            $this->messages = $mailbox->messages(
                search: $this->search,
                folder: $this->folder,
                filter: $this->filter,
            )['data'];
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
            $this->messages = [];
            $this->error = $exception->getMessage();
        }
    }

    public function updatedSearch(): void
    {
        $this->refreshMailbox();
    }

    public function selectFolder(string $folder): void
    {
        abort_unless(in_array($folder, AdminProMailboxService::FOLDERS, true), 422);
        $this->authorizeAdminPro();
        $this->folder = $folder;
        $this->selectedMessage = null;
        $this->refreshMailbox();
    }

    public function selectFilter(string $filter): void
    {
        abort_unless(in_array($filter, AdminProMailboxService::FILTERS, true), 422);
        $this->authorizeAdminPro();
        $this->filter = $filter;
        $this->selectedMessage = null;
        $this->refreshMailbox();
    }

    public function openMessage(int $uid): void
    {
        $this->authorizeAdminPro();

        try {
            $this->selectedMessage = app(AdminProMailboxService::class)->message($uid, $this->folder);
            $this->refreshMailbox();
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
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
            system_log('admin_pro_mail_send', 'Користувач із поштовим доступом надіслав лист через post@omc.pl.ua.');
            $this->cancelCompose();
            Notification::make()->success()->title('Лист надіслано')->send();
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
            Notification::make()->danger()->title('Не вдалося надіслати лист')->body($exception->getMessage())->send();
        }
    }

    public function deleteMessage(int $uid): void
    {
        $this->authorizeAdminPro();

        try {
            $permanently = $this->folder === 'trash';
            app(AdminProMailboxService::class)->delete($uid, $this->folder, $permanently);
            system_log('admin_pro_mail_delete', 'Користувач із поштовим доступом видалив лист зі скриньки post@omc.pl.ua.');
            $this->selectedMessage = null;
            $this->refreshMailbox();
            Notification::make()->success()->title($permanently ? 'Лист видалено назавжди' : 'Лист переміщено у видалені')->send();
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    public function moveMessage(int $uid, string $target): void
    {
        abort_unless(in_array($target, AdminProMailboxService::FOLDERS, true), 422);
        $this->authorizeAdminPro();

        try {
            app(AdminProMailboxService::class)->move($uid, $target, $this->folder);
            system_log('admin_pro_mail_move', 'Користувач із поштовим доступом перемістив лист у папку '.$target.'.');
            $this->selectedMessage = null;
            $this->refreshMailbox();
            Notification::make()->success()->title('Лист переміщено')->send();
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    public function toggleFlag(int $uid, bool $flagged): void
    {
        $this->authorizeAdminPro();

        try {
            app(AdminProMailboxService::class)->flag($uid, $flagged, $this->folder);
            if ($this->selectedMessage && $this->selectedMessage['uid'] === $uid) {
                $this->selectedMessage['flagged'] = $flagged;
            }
            $this->refreshMailbox();
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    public function markUnread(int $uid): void
    {
        $this->authorizeAdminPro();

        try {
            app(AdminProMailboxService::class)->mark($uid, false, $this->folder);
            $this->selectedMessage = null;
            $this->refreshMailbox();
        } catch (Throwable $exception) {
            $this->reportMailboxFailure($exception);
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    private function reportMailboxFailure(Throwable $exception): void
    {
        try {
            report($exception);
        } catch (Throwable) {
            // Keep the mailbox UI available if logging is temporarily unwritable.
        }
    }

    private function authorizeAdminPro(): void
    {
        abort_unless(static::canAccess(), 403, 'Немає доступу до пошти AdminPro.');
    }
}
