<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['participants', 'messages'];

    // Set default values for JSON attributes if they are not provided
    protected $attributes = [
        'participants' => '[]',  // Default to an empty array
        'messages' => '[]',      // Default to an empty array
    ];

    protected $casts = [
        'participants' => 'array',  // Automatically cast JSON to array
        'messages' => 'array',      // Automatically cast JSON to array
    ];
}
