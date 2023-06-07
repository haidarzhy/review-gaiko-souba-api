<?php

namespace App\Models;

use App\Models\Qq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QAnsInputType extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'input', 'status', 'order'];

    public function qq()
    {
        return $this->hasMany(Qq::class);
    }
}
