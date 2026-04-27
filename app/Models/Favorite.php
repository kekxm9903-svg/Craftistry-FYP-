<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'artist_id'];

    /**
     * The user who favorited
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The artist who was favorited
     */
    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}