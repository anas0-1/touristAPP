<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'name',
        'description',
        'time',
        'duration',
        'location',
        'program_id'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}