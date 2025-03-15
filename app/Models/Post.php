<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'title',
        'slug',
        'excerpt',
        'content',
        'user_id',
        'created_at',
        'updated_at',
    ];
}
