<?php

namespace App\Models;

use App\Models\Cct;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cc extends Model
{
    use HasFactory;
    protected $fillable = ['cn', 'ed_month', 'ed_year', 'cvv', 'fn', 'ln', 'cct_id'];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function cct()
    {
        return $this->belongsTo(Cct::class);
    }
}
