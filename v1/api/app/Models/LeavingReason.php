<?php

namespace App\Models;

use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeavingReason extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'order', 'status'];

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

}
