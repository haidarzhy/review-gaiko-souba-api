<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan',
        'price',
        'product_code',
        'gid',
        'rst',
        'ap',
        'ec',
        'god',
        'cod',
        'am',
        'tx',
        'sf',
        'ta',
        'issue_id',
        'ps',
        'acid'
    ];


    public function user()
    {
        return $this->hasOne(User::class);
    }

}
