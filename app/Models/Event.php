<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'author_id', 'start_time', 'end_time', 'image'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user');
    }


    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function invites()
    {
        return $this->hasMany(Invite::class);
    }
}
