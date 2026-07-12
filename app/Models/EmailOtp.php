<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    protected $table = 'email_otps';

    protected $fillable = ['email', 'name', 'otp_hash', 'attempt_count', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
