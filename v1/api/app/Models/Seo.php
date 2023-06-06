<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    use HasFactory;
    protected $fillable = ['og_type', 'og_locale', 'og_title', 'og_description', 'og_url', 'og_image', 'og_image_width', 'og_image_height'];
}
