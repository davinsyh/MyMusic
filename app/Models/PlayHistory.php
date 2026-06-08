<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayHistory extends Model
{
    protected $fillable = [
        'user_id',
        'yt_track_id',
        'track_title',
        'artist_name',
        'thumbnail_url',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];
}
