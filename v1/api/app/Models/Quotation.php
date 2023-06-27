<?php

namespace App\Models;

use App\Models\Quotation;
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

    public function parent()
    {
        return $this->belongsTo(Quotation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Quotation::class, 'parent_id');
    }
    
}
