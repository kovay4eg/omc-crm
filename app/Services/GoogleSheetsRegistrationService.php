<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleSheetsRegistrationService
{
    public function __construct(private readonly GoogleSheetsReader $reader) {}

    /**
     * @return array{imported:int, updated:int, skipped:int, total_rows:int, sheet_name:string, synced_at:string}
     */
    public function sync(User $user, Event $event): array
    {
        $sheet = $this->reader->read(
            $user,
            (string) $event->google_sheet_url,
            $event->google_sheet_name,
        );
        $spreadsheetId = $sheet['spreadsheet_id'];
        $sheetName = $sheet['sheet_name'];
        $rows = $sheet['rows'];

        if ($rows === []) {
            return $this->finish($event, $user, $sheetName, 0, 0, 0, 0);
        }

        $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), array_shift($rows));
        $columns = [
            'name' => $this->columnIndex($headers, [
                'піб', 'прізвище ім я по батькові', 'прізвище та ім я', 'ім я',
                'фио', 'имя', 'full name', 'name',
            ]),
            'email' => $this->columnIndex($headers, [
                'електронна пошта', 'електронна адреса', 'e mail', 'email',
            ]),
            'phone' => $this->columnIndex($headers, [
                'номер телефону', 'телефон', 'мобільний телефон', 'phone number', 'phone',
            ]),
        ];

        if ($columns['name'] === null) {
            throw ValidationException::withMessages([
                'google_sheet_url' => ['У першому рядку таблиці не знайдено колонку «ПІБ» або «Імʼя».'],
            ]);
        }

        $stats = DB::transaction(function () use ($event, $rows, $columns, $spreadsheetId, $sheetName): array {
            $stats = ['imported' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($rows as $row) {
                $name = $this->cell($row, $columns['name']);
                $email = $this->cell($row, $columns['email']);
                $phone = $this->cell($row, $columns['phone']);

                if ($name === '') {
                    $stats['skipped']++;

                    continue;
                }

                $externalIdentity = trim((string) ($row[0] ?? ''));
                if ($externalIdentity === '') {
                    $externalIdentity = hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE) ?: '');
                }

                $rowKey = hash('sha256', implode('|', [
                    $spreadsheetId,
                    $sheetName,
                    $externalIdentity,
                ]));

                $existing = $event->registrations()
                    ->where('google_row_key', $rowKey)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone !== '' ? $phone : null,
                        'external_url' => $event->google_form_url,
                    ]);
                    $stats['updated']++;

                    continue;
                }

                if ($this->duplicateExists($event, $email, $phone)) {
                    $stats['skipped']++;

                    continue;
                }

                $event->registrations()->create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone !== '' ? $phone : null,
                    'source' => 'google_form',
                    'external_url' => $event->google_form_url,
                    'google_row_key' => $rowKey,
                ]);
                $stats['imported']++;
            }

            return $stats;
        });

        return $this->finish(
            $event,
            $user,
            $sheetName,
            $stats['imported'],
            $stats['updated'],
            $stats['skipped'],
            count($rows),
        );
    }

    private function normalizeHeader(string $value): string
    {
        return trim((string) preg_replace('/[^\pL\pN]+/u', ' ', Str::lower($value)));
    }

    /** @param array<int, string> $headers */
    private function columnIndex(array $headers, array $aliases): ?int
    {
        foreach ($headers as $index => $header) {
            foreach ($aliases as $alias) {
                if ($header === $alias || str_contains($header, $alias)) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function cell(array $row, ?int $index): string
    {
        return $index === null ? '' : trim((string) ($row[$index] ?? ''));
    }

    private function duplicateExists(Event $event, string $email, string $phone): bool
    {
        if ($email === '' && $phone === '') {
            return false;
        }

        return $event->registrations()
            ->where(function ($query) use ($email, $phone): void {
                if ($email !== '') {
                    $query->whereRaw('LOWER(email) = ?', [Str::lower($email)]);
                }

                if ($phone !== '') {
                    $method = $email !== '' ? 'orWhere' : 'where';
                    $query->{$method}('phone', $phone);
                }
            })
            ->exists();
    }

    private function finish(
        Event $event,
        User $user,
        string $sheetName,
        int $imported,
        int $updated,
        int $skipped,
        int $totalRows,
    ): array {
        $syncedAt = now();
        $event->updateQuietly([
            'google_sheet_name' => $sheetName,
            'google_sync_user_id' => $user->getKey(),
            'google_last_synced_at' => $syncedAt,
        ]);

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'total_rows' => $totalRows,
            'sheet_name' => $sheetName,
            'synced_at' => $syncedAt->toIso8601String(),
        ];
    }
}
