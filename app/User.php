<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Carbon\Carbon;
use Hash;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = "m_users";
    protected $primaryKey = "us_id";
    public $remember_token = false;
    protected $hidden = [
        'password', 'remember_token',
    ];
    const CREATED_AT       = 'us_created';
    const UPDATED_AT       = 'us_updated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'us_name', 'us_email', 'password','us_created','us_updated','us_id',
    ];
     public function findForPassport($username)
    {
        return $this->where('us_email', $username)->first();
    }
    public function validateForPassportPasswordGrant($password){
        return Hash::check($password, $this->us_password);
    }
}
