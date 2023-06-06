<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['site_name', 'description', 'keywords', 'site_logo', 'icon', 'email', 'footer_text', 'site_size', 'cache_size'];
}
