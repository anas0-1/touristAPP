<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['program_id', 'url'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

