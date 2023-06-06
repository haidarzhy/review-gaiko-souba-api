<?php

namespace App\Models;

use App\Models\Qa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Measure extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'status', 'order'];

    public function qas()
    {
        return $this->hasMany(Qa::class);
    }
}
