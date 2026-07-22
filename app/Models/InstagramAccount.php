<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramAccount extends Model
{
    protected $fillable = [
        'username',
        'name',
        'instagram_id',
        'access_token',
        'token_expires_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'token_expires_at' => 'datetime',
    ];

    protected $table = 'instagram_accounts';
}
