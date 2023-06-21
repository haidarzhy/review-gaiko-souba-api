<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationFormulaCondition extends Model
{
    use HasFactory;
    protected $fillable = ['math_symbol_id', 'situation', 'result', 'quotation_formula_id'];
}
