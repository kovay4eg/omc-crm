<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-m-users';

    /**
     * ТІЛЬКИ АДМІН
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->getActiveRole() === 'admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->getActiveRole() === 'admin';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->getActiveRole() === 'admin';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->getActiveRole() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),

            TextInput::make('email')->email()->required(),

            TextInput::make('password')
                ->password()
                ->required(fn ($context) => $context === 'create')
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state)),

            Select::make('role')
                ->label('Роль')
                ->options([
                    'admin' => '👑 Адмін',
                    'editor' => '🛠 Редактор',
                    'content' => '🎨 Контент-мейкер',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Імʼя')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Роль')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin' => '👑 Адмін',
                        'editor' => '🛠 Редактор',
                        'content' => '🎨 Контент-мейкер',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Створено')->dateTime(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
