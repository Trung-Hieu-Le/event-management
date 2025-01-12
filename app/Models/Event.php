<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'description', 'start_time', 'end_time', 'location', 'latitude', 'longitude', 'type', 'author_id', 'delete'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user');
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
