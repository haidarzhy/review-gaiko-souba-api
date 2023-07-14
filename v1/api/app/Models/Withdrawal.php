<?php

namespace App\Models;

use App\Models\User;
use App\Models\LeavingReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Withdrawal extends Model
{
    use HasFactory;
    protected $fillable = ['company_name', 'email', 'month_to_withdrawl', 'leaving_reason_id', 'user_id', 'status', 'order'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaving_reason()
    {
        return $this->belongsTo(LeavingReason::class);
    }


}
