<?php

namespace App\Models;

use App\Models\QuotationFormula;
use App\Models\QuotationCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;
    protected $fillable = ['q_name', 'condition', 'base_amount', 'quantity', 'unit_price', 'amount', 'total', 'formula_total', 'parent_id'];

    public function quotationConditions()
    {
        return $this->hasMany(QuotationCondition::class);
    }

    public function quotationFormulas()
    {
        return $this->hasMany(QuotationFormula::class);
    }
    
}
