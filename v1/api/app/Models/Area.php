<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'order', 'status'];


    public function users()
    {
        return $this->belongsToMany(User::class);
    }


}
