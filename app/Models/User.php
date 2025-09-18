<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'title',
        'email',
        'phone',
        'gender',
        'password',
        'image',
        'address',
        'latitude',
        'longitude',
        'role_id',
        'branch_id',
        'otp',
        'description',
        'status',
        'device_token',
        'stripe_access_token',
        'facebook',
        'instagram',
        'linkdin',
        'twitter',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    // public function getCreatedAtAttribute($date)
    // {
    //     return Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('M d Y');
    // }
    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('assets/user-image/'. $value);
        } else {
            return asset('assets/user-image/default-image.png');
        }
    } 
    public function getCategoryData(){
        return $this->hasMany('App\Models\UserCategory','user_id','id')->with('getCategoryData');
    }
}
