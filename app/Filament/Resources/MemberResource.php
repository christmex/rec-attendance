<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Member;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MemberResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MemberResource\RelationManagers;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Jemaat';

    protected static ?string $navigationGroup = 'Pengelolaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->label('Nama Jemaat')
                        ->maxLength(255),
                    Forms\Components\Select::make('parent_id')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->label('Nama Keluarga')
                        ->helperText('Contoh: Keluarga Rusdi')
                        ->optionsLimit(10)
                        ->relationship(
                            name: 'parent', 
                            titleAttribute: 'name', 
                            ignoreRecord: true,
                            modifyQueryUsing: fn (Builder $query) => $query->whereNull('parent_id')
                        )
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->label('Nama Keluarga')
                                ->placeholder('Contoh: Keluarga Rusdi')
                                ->maxLength(255),
                        ])
                        ->editOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->label('Nama Keluarga')
                                ->placeholder('Contoh: Keluarga Rusdi')
                                ->maxLength(255),
                        ]),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->label('WA/HP')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat')
                        ->columnSpanFull(),
                ])->columnSpanFull()->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jemaat')
                    ->description(fn (Member $record): string => $record->parent->name)
                    ->searchable(),
                // Tables\Columns\TextColumn::make('parent.name')
                //     ->label('Nama Keluarga')
                //     ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('WA/HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton()
                    ->before(function (Action $action,Member $record) {
                        if ($record->attendances()->exists()){
                            Notification::make()
                                ->danger()
                                ->title("Terdapat Data di Data Kehadiran")
                                ->send();
                                $action->halt();
                        }

                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMembers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('parent_id');
    }
}
