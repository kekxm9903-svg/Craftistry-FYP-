<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = ['user_id', 'category', 'subject', 'message', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}