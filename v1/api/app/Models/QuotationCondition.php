<?php

namespace App\Models;

use App\Models\Qa;
use App\Models\Qq;
use App\Models\Quotation;
use App\Models\MathSymbol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationCondition extends Model
{
    use HasFactory;
    protected $fillable = ['condition_id', 'qq_id', 'math_symbol_id', 'qa_id', 'qa_value', 'qa_any', 'quotation_id', 'created_at', 'updated_at'];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function qq()
    {
        return $this->belongsTo(Qq::class);
    }

    public function qa()
    {
        return $this->belongsTo(Qa::class);
    }

    public function mathSymbol()
    {
        return $this->belongsTo(MathSymbol::class);
    }

}
