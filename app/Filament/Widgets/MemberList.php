<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Event;
use App\Models\Member;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
// use Livewire\Attributes\On; 

class MemberList extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    // protected $listeners = ['updateMemberListTable' => '$refresh'];

    // 
    // public $events;

    // when we use event with form blade uncommend this
    public ?array $data = [];

    public function updatingData($property, $value)
    {
        // dd($property,$value);
        // $property: The name of the current property being updated
        // $value: The value about to be set to the property

        // app.filament.widgets.member-list
        // dd($property);
        // $this->dispatch('re-render-table');
 
        // if ($property === 'postId') {
        //     throw new Exception;
        // }
    }

    // when we use event with form blade uncommend this
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Radio::make('event_id')
                    ->label('Kegiatan')
                    ->live()
                    ->required()
                    // ->default(fn()=>
                    //     Event::where(function($query){
                    //         $query->where('start', '<=', date('Y-m-d H:i:s'))
                    //             ->where('end', '>=', date('Y-m-d H:i:s'));
                    //         })->first()->id
                    // )
                    ->options(
                        // dd($this)
                        Event::where(function($query){
                            $query->where('start', '<=', date('Y-m-d H:i:s'))
                                ->where('end', '>=', date('Y-m-d H:i:s'));
                            })->get()->pluck('name','id')->toArray()
                        )
                    ->descriptions(
                        Event::where(function($query){
                            $query->where('start', '<=', date('Y-m-d H:i:s'))
                                ->where('end', '>=', date('Y-m-d H:i:s'));
                            })->get()->pluck('description','id')->toArray()
                        )
                    ->columnSpanFull()
                    ->columns(2)
                    ->gridDirection('row')
            ])
            // ->dispatchEvent('updateMemberListTable')
            ->statePath('data');
    }

    // #[On('re-render-table')] 
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => 
                    Member::query()
                        ->whereNotNull('parent_id')
                        ->orderBy('parent_id','desc')
                        // ->orderBy('name')
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
                    ->toggleable()
                    ->toggledHiddenByDefault()
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
                    ->label('Absen Masuk')
                    ->hidden(function(Model $record){
                        if(count($this->data)){
                            if($record->attendances->count()){
                                if($record->attendances->where('event_id',$this->data['event_id'])->count()){
                                    return true;
                                }
                            }
                            return false;
                        }
                        return true;
                    })
                    ->action(function(Model $record){
                        if(!count($this->data)){
                            Notification::make()
                                ->danger()
                                ->title('Pilih Kegiatan Terlebih dahulu')
                                ->send();
                        }else {
                            if(!$record->attendances->where('event_id',$this->data['event_id'])->count()){
                                Attendance::firstOrCreate([
                                    'event_id' => $this->data['event_id'],
                                    'member_id' => $record->id,
                                    'check_in' => date('Y-m-d H:i:s'),
                                ]);
                                Notification::make()
                                    ->success()
                                    ->title($record->name.' Berhasil Melalukan Absen Masuk')
                                    ->send();
                            }else {
                                Notification::make()
                                    ->warning()
                                    ->title($record->name.' Sudah Absen Di Kegiatan Terpilih')
                                    ->send();
                            }
                        }
                    }),
                Tables\Actions\Action::make('UnCheckIn')
                    ->color('danger')
                    ->label('Batalkan')
                    ->hidden(function(Model $record){
                        if(count($this->data)){
                            if($record->attendances->count()){
                                if(!$record->attendances->where('event_id',$this->data['event_id'])->count()){
                                    return true;
                                }
                            }
                            return false;
                        }
                        return true;
                    })
                    ->action(function(Model $record){
                        if(!count($this->data)){
                            Notification::make()
                                ->danger()
                                ->title('Pilih Kegiatan Terlebih dahulu')
                                ->send();
                        }else {
                            if($record->attendances->where('event_id',$this->data['event_id'])->count()){
                                Attendance::where('event_id',$this->data['event_id'])->where('member_id',$record->id)->delete();
                                Notification::make()
                                    ->info()
                                    ->title($record->name.' Berhasil Melalukan Pembataln absen masuk')
                                    ->send();
                            }else {
                                Notification::make()
                                    ->warning()
                                    ->title($record->name.' Belum Absen Di Kegiatan Terpilih')
                                    ->send();
                            }
                        }
                    }),
            ])
            // ->filters([
            //     Filter::make('is_family')
            //         ->label('Tampilkan Hanya Berkeluarga')
            //         ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
            // ])
            ->deferLoading();
    }
}
