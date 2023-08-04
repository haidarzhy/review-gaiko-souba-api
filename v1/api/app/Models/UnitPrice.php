<?php

namespace App\Models;

use App\Models\UnitPriceDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitPrice extends Model
{
    use HasFactory;

    public function unitPriceDetails()
    {
        return $this->hasMany(UnitPriceDetail::class)->with(['area']);
    }
}
