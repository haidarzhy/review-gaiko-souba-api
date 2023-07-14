<?php

namespace App\Models;

use App\Models\Cc;
use App\Models\Area;
use App\Models\Contact;
use App\Models\Inquiry;
use App\Models\Withdrawal;
use App\Models\PaymentInfo;
use App\Models\Construction;
use App\Models\PaymentMethod;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'kana_name',
        'address01',
        'address02',
        'company_name',
        'email',
        'tel',
        'url',
        'password',
        'company_url',
        'payment_method_id',
        'cc_id',
        'role_id',
        'status',
        'order',
        'last_login_date',
        'payment_info_id'
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


    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }

    public function constructions()
    {
        return $this->belongsToMany(Construction::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function cc()
    {
        return $this->belongsTo(Cc::class);
    }

    public function paymentInfo()
    {
        return $this->belongsTo(PaymentInfo::class);
    }

    public function ccWithCct()
    {
        return $this->hasOne(Cc::class)->with('cct');
    }

    public function contacts()
    {
        return $this->belongsTo(Contact::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }


}
