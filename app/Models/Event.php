<?php

namespace App\Models;

use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('ActiveEvent', function (Builder $builder) {
            // $from = date('Y-m-d'). ' 00:00:00';
            $from = date('Y-m-d H:i:s');
            $to = date('Y-m-d'). ' 23:59:59';
            $builder->where(function($query) use ($from, $to){
                $query->where('start', '<=', $from)
                    ->where('end', '>=', $from);
                });
                // ->where('start', )
                // ->where('end', )
                // ;
        });
    }

    public function getDescriptionAttribute()
    {
        return new HtmlString('Mulai: '.$this->start->translatedFormat('l, d F Y \\J\\a\\m H:i').'<br> Berakhir: '.$this->end->translatedFormat('l, d F Y \\J\\a\\m H:i'));
    }
    public function attendances() :HasMany{
        return $this->hasMany(Attendance::class);
    }
}
