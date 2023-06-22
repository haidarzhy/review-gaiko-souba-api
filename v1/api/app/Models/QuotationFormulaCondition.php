<?php

namespace App\Models;

use App\Models\MathSymbol;
use App\Models\QuotationFormula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationFormulaCondition extends Model
{
    use HasFactory;
    protected $fillable = ['math_symbol_id', 'situation', 'result', 'quotation_formula_id'];

    public function quotationFormula()
    {
        return $this->belongsTo(QuotationFormula::class);
    }

    public function mathSymbol()
    {
        return $this->belongsTo(MathSymbol::class);
    }

}
