<?php

/**
 * Class User
 *
 * @property int $id
 * @property int $role_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Role $role
 * @property Collection|SellerDetail[] $seller_details
 *
 * @package App\Models
 */

namespace App\Models;

use Carbon\Carbon;
//use Illuminate\Database\Eloquent\Model;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens,
        HasFactory,
        Notifiable;

    protected $table = 'users';
    protected $casts = [
        'role_id' => 'int'
    ];
    protected $dates = [
        'email_verified_at'
    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function seller_details()
    {
        return $this->hasMany(SellerDetail::class);
    }

}
