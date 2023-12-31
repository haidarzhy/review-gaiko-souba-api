<?php

namespace App\Models;

use App\Models\User;
use App\Models\InquiryQaAns;
use App\Models\InquiryQuote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'name', 'kata_name', 'address01', 'address02', 'company_name', 'email', 'tel', 'construction_schedule', 'total', 'confirm', 'status', 'order', 'user_id'];

    public function inquiryQaAns()
    {
        return $this->hasMany(InquiryQaAns::class)->with(['qOnly', 'qa']);
    }

    public function inquiryQuotes()
    {
        return $this->hasMany(InquiryQuote::class)->with('quotation');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
