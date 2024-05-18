<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\AttendanceResource;

class ManageAttendances extends ManageRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data, string $model): Model {
                    foreach ($data['member_id'] as $key => $value) {
                        if ($key === array_key_last($data['member_id'])) {
                            return $model::create([
                                'event_id' => $data['event_id'],
                                'member_id' => $value,
                                'check_in' => $data['check_in'],
                            ]);
                        }else{
                            $model::create([
                                'event_id' => $data['event_id'],
                                'member_id' => $value,
                                'check_in' => $data['check_in'],
                            ]);
                        }
                    }
                }),
        ];
    }
}
