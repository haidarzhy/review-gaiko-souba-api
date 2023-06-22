<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationCondition extends Model
{
    use HasFactory;
    protected $fillable = ['qq_id', 'math_symbol_id', 'qa_id', 'quotation_id', 'created_at', 'updated_at'];
}
