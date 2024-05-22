<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use App\Models\Event;
use App\Models\Member;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class MemberList extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public $events;

    // when we use event with form blade uncommend this
    public ?array $data = [];

    // when we use event with form blade uncommend this
    public function form(Form $form): Form
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
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => 
                    Member::query()
                        ->whereNotNull('parent_id')
                        ->orderBy('parent_id')
                    // ->select('students.*')
                    // ->selectSub(
                    //     function ($query) {
                    //         $query->selectRaw('SUM(cost)')
                    //             ->from('student_spp_bill')
                    //             ->whereColumn('student_id', 'students.id');
                    //     },
                    //     'student_spp_bill_sum_cost'
                    // )
                    // ->orderByDesc('student_spp_bill_sum_cost')
            )
            ->heading(null)
            // ->paginated([5])
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Jemaat')
                    ->description(fn (Member $record): string => $record->parent->name)
                    ->searchable(['name']),
                // TextColumn::make('parent.name')
                //     ->label('Nama Keluarga')
                //     ->sortable(),
                TextColumn::make('phone')
                    ->label('WA/HP')
                    ->searchable(),
                // ToggleColumn::make('')
                //     // ->hidden(fn()=>empty($this->events))
                //     ->beforeStateUpdated(function ($record, $state) {
                //         // dd($record->attendaces()->attach(),$state);
                //         if(empty($this->events)){
                //             // Notification::make()
                //             //     ->warning()
                //             //     ->title('Pilih Kegiatan Terlebih dahulu')
                //             //     ->send();
                //         }else {
                //             dd($record,$state, $this->events);
                //         }
                //         // Runs before the state is saved to the database.
                //     })
            ])
            ->searchPlaceholder('Cari (Nama/Keluarga)')
            ->searchOnBlur()
            ->actions([
                Tables\Actions\Action::make('CheckIn')
                    // ->hidden(fn(Model $record)=> count($this->data) && !$record->attendances->where('event_id',$this->events)->count())
                    ->action(function(Model $record){
                        if(!count($this->data)){
                            Notification::make()
                                ->danger()
                                ->title('Pilih Kegiatan Terlebih dahulu')
                                ->send();
                        }else {
                            if(!$record->attendances->where('event_id',$this->data['event_id'])->count()){
                                Attendance::create([
                                    'event_id' => $this->data['event_id'],
                                    'member_id' => $record->id,
                                    'check_in' => date('Y-m-d H:i:s'),
                                ]);
                                Notification::make()
                                    ->success()
                                    ->title($record->name.' Berhasil Melalukan Check-in')
                                    ->send();
                            }else {
                                Notification::make()
                                    ->warning()
                                    ->title($record->name.' Sudah Absen Di Kegiatan Terpilih')
                                    ->send();
                            }
                        }
                    })
            ])
            ->deferLoading();
    }
}
