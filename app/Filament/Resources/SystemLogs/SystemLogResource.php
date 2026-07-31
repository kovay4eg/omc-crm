<?php

namespace App\Filament\Resources\SystemLogs;

use App\Models\SystemLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemLogResource extends Resource
{
    protected static ?string $model = SystemLog::class;

    protected static ?string $navigationLabel = 'Логи';
    protected static ?int $navigationSort = 99;

    public static function canViewAny(): bool
    {
        return auth()->user()?->getActiveRole() === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->getActiveRole() === 'admin';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('action')
                    ->label('Дія')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'delete_event' => 'danger',
                        'update_event' => 'warning',
                        'create_event' => 'success',
                        'login_failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->wrap(),

                // 👇 КОРИСТУВАЧ
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Користувач')
                    ->default('-'),

                // 👇 РОЛЬ
                Tables\Columns\TextColumn::make('user.role')
                    ->label('Роль')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        default => 'gray',
                    })
                    ->default('-'),

                Tables\Columns\TextColumn::make('ip')
                    ->label('IP'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Час')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SystemLogs\SystemLogResource\Pages\ListSystemLogs::route('/'),
        ];
    }
}