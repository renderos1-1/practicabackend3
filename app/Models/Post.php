<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //
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
