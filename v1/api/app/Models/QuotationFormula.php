<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationFormula extends Model
{
    use HasFactory;
    protected $fillable = ['formula', 'formula_total_id', 'quotation_id'];
}
