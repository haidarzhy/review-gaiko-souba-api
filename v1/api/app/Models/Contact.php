<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kana_name',
        'company_name',
        'tel',
        'email',
        'address01',
        'address02',
        'content',
        'site',
        'ip',
        'lat',
        'lon',
        'continent',
        'country',
        'regionName',
        'city',
        'mobile',
        'user_id',
        'status',
        'order',
        'new'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

}
