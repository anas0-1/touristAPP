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

    /**
     * Get the user that owns the program.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the activities associated with the program.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the media associated with the program.
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get the applications associated with the program.
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
