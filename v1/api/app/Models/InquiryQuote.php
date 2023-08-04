<?php

namespace App\Models;

use App\Models\Inquiry;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InquiryQuote extends Model
{
    use HasFactory;

    protected $fillable = ['quotation_id', 'quantity', 'unit_price', 'amount', 'inquiry_id'];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

}
