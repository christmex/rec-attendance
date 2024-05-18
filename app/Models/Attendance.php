<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    public function event() :BelongsTo{
        return $this->belongsTo(Event::class);
    }

    public function member() :BelongsTo{
        return $this->belongsTo(Member::class);
    }
}
