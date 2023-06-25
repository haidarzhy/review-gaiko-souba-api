<?php

namespace App\Models;

use App\Models\Cc;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cct extends Model
{
    use HasFactory;
    protected $fillable = ['ccty', 'order', 'status'];

    public function ccs()
    {
        return $this->hasMany(Cc::class);
    }
}
