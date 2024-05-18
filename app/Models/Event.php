<?php

namespace App\Models;

use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
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

    public function getDescriptionAttribute()
    {
        return new HtmlString('Mulai: '.$this->start->translatedFormat('l, d F Y \\J\\a\\m H:i').'<br> Berakhir: '.$this->end->translatedFormat('l, d F Y \\J\\a\\m H:i'));
    }

    // public function attendances() :HasMany {
    //     return $this->hasMany();
    // }
}
