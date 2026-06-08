<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedLibrary extends Model
{
    protected $fillable = [
        'user_id',
        'yt_track_id',
        'track_title',
        'artist_name',
        'thumbnail_url',
    ];

    protected $casts = [
        'saved_at' => 'datetime',
    ];
}
