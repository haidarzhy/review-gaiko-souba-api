<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationFormula extends Model
{
    use HasFactory;
    protected $fillable = ['formula', 'quotation_id'];
}
