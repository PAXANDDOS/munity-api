<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'content',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'event_id' => 'integer',
        'content' => 'string',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function event()
    {
        return $this->hasOne(Event::class);
    }
}
