<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'user_id', 
        'name', 
        'description', 
        'duration', 
        'location', 
        'price',
        'starting_date'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'starting_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}