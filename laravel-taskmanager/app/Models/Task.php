<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    public $timestamps = false;
    

    protected $fillable = ['title', 'description', 'due_date', 'user_id', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}