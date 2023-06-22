<?php

namespace App\Models;

use App\Models\QuotationCondition;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuotationFormulaCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MathSymbol extends Model
{
    use HasFactory;

    public function quotationConditions()
    {
        return $this->hasMany(QuotationCondition::class);
    }

    public function quotationFormulaConditions()
    {
        return $this->hasMany(QuotationFormulaCondition::class);
    }

}
