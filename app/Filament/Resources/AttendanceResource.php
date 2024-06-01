<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Event;
use App\Models\Member;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Radio;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $navigationGroup = 'Pengelolaan';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Radio::make('event_id')
                    ->label('Kegiatan')
                    ->required()
                    ->options(Event::all()->pluck('name','id')->toArray())
                    ->descriptions(Event::all()->pluck('description','id')->toArray())
                    ->columnSpanFull()
                    ->columns(2)
                    ->gridDirection('row')
                    ,
                CheckboxList::make('member_id')
                    ->searchable()
                    ->required()
                    ->label('Jemaat')
                    ->columnSpanFull()
                    ->bulkToggleable()
                    ->columns(4)
                    ->gridDirection('row')
                    ->options(Member::whereNotNull('parent_id')->get()->pluck('name','id')->toArray())
                    ->descriptions(Member::whereNotNull('parent_id')->get()->pluck('parent.name','id')->toArray()),
                Forms\Components\DateTimePicker::make('check_in')
                    ->default('now')
                    ->label('Kedatangan')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Nama Kegiatan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.name')
                    ->label('Nama Jemaat')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->dateTime('l, d F Y \\J\\a\\m H:i:s')
                    ->label('Kedatangan')
                    ->sortable(),
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
                SelectFilter::make('event_id')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->options(Event::all()->pluck('name_with_desc','id')->toArray()),
                SelectFilter::make('member_id')
                    ->label('Nama Jemaat')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(10)
                    ->relationship('member','name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageAttendances::route('/'),
        ];
    }
}
