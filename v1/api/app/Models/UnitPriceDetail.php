<?php

namespace App\Models;

use App\Models\Area;
use App\Models\UnitPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitPriceDetail extends Model
{
    use HasFactory;

    protected $fillable = ['large_classification', 'minor_classification', 'content', 'specification', 'area_id', 'amount', 'unit_price_id', 'order', 'status'];

    public function unitPrice()
    {
        return $this->belongsTo(UnitPrice::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

}
