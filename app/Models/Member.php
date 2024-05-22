<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    public function parent() :BelongsTo{
        return $this->belongsTo(Member::class);
    }
    public function attendances() :HasMany{
        return $this->hasMany(Attendance::class);
    }
}
