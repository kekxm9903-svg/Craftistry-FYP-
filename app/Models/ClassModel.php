<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes'; // Because "Class" is reserved

    protected $fillable = [
        'artist_id',
        'title',
        'description',
        'price',
        'schedule',
    ];

    // Relationships
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
