<?php

namespace App\Models;

use App\Models\Qq;
use App\Models\Measure;
use App\Models\InquiryQaAns;
use App\Models\QAnsInputType;
use App\Models\QuotationCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qa extends Model
{
    use HasFactory;
    protected $fillable = ['suffix', 'label', 'image', 'quantity', 'unit_price', 'qq_id', 'status', 'order'];

    public function qq()
    {
        return $this->belongsTo(Qq::class);
    }

    public function quotationConditions()
    {
        return $this->hasMany(QuotationCondition::class);
    }

    public function inquiryQaAns()
    {
        return $this->hasOne(InquiryQaAns::class);
    }
}
