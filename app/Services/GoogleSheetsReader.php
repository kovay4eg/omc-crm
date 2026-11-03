<?php

namespace App\Services;

use App\Models\User;
use Google\Service\Sheets;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleSheetsReader
{
    public function __construct(private readonly GoogleApiClientFactory $clients) {}

    /**
     * @return array{spreadsheet_id:string, sheet_name:string, rows:array<int, array<int, mixed>>}
     */
    public function read(User $user, string $url, ?string $requestedSheetName = null): array
    {
        if (! preg_match('~docs\.google\.com/spreadsheets/d/([a-zA-Z0-9_-]+)~', $url, $matches)) {
            throw ValidationException::withMessages([
                'google_sheet_url' => ['Вставте повне посилання на Google Sheets із відповідями форми.'],
            ]);
        }

        $spreadsheetId = $matches[1];

        try {
            $sheets = new Sheets($this->clients->make($user));
            $sheetName = $requestedSheetName ?: $this->firstSheetName($sheets, $spreadsheetId);
            $range = "'".str_replace("'", "''", $sheetName)."'";

            return [
                'spreadsheet_id' => $spreadsheetId,
                'sheet_name' => $sheetName,
                'rows' => $sheets->spreadsheets_values->get($spreadsheetId, $range)->getValues() ?? [],
            ];
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'google_sheet_url' => ['Не вдалося прочитати таблицю. Перевірте доступ Google і посилання.'],
            ]);
        }
    }

    private function firstSheetName(Sheets $sheets, string $spreadsheetId): string
    {
        $spreadsheet = $sheets->spreadsheets->get($spreadsheetId, [
            'fields' => 'sheets.properties.title',
        ]);
        $name = $spreadsheet->getSheets()[0]?->getProperties()?->getTitle();

        if (blank($name)) {
            throw ValidationException::withMessages([
                'google_sheet_url' => ['У таблиці не знайдено аркушів із відповідями.'],
            ]);
        }

        return $name;
    }
}
