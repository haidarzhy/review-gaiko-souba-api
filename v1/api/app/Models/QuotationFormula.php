<?php

namespace App\Models;

use App\Models\Quotation;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuotationFormulaCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationFormula extends Model
{
    use HasFactory;
    protected $fillable = ['formula', 'formula_total_id', 'quotation_id'];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function quotationFormulaConditions()
    {
        return $this->hasMany(QuotationFormulaCondition::class);
    }

}
