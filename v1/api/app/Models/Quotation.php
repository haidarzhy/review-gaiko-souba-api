<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;
    protected $fillable = ['q_name', 'quantity', 'unit_price', 'amount', 'total', 'formula_total', 'parent_id'];
}
