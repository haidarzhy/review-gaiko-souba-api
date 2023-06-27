<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryQaAns extends Model
{
    use HasFactory;
    protected $fillable = ['inquiry_id', 'q_index', 'qq_id', 'qa_id', 'qa_value'];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }
}
