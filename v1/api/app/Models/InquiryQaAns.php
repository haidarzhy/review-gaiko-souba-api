<?php

namespace App\Models;

use App\Models\Qa;
use App\Models\Qq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InquiryQaAns extends Model
{
    use HasFactory;
    protected $fillable = ['inquiry_id', 'q_index', 'qq_id', 'qa_id', 'qa_value'];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function qq()
    {
        return $this->belongsTo(Qq::class)->with('qas');
    }

    public function qOnly()
    {
        return $this->belongsTo(Qq::class, 'qq_id');
    }

    public function qa()
    {
        return $this->belongsTo(Qa::class);
    }

}
