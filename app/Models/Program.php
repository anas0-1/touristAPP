<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'duration', 'images'];
    
    protected $casts = [
        'images' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}