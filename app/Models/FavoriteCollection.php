<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'yt_id',
        'title',
        'author',
        'type',
        'thumbnail_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
