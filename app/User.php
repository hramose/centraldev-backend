<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'authentication';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'firstname', 'lastname', 'email', 'password', 'login_attempt', 'email_confirmed', 'account_approved', 'registered_ip', 'last_ip', 'oauth_provider', 'oauth_provider_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'password', 'remember_token', 'login_attempt', 'registered_ip', 'last_ip', 'twofa_secretkey', 'oauth_provider', 'oauth_provider_id'
    ];
}
