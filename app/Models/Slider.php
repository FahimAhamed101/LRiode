<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'tagline',
        'title',
        'subtitle',
        'image',
        'link',
        'status'
    ];
}
