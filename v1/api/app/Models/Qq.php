<?php

namespace App\Models;

use App\Models\Qa;
use App\Models\QAnsInputType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qq extends Model
{
    use HasFactory;
    protected $fillable = ['q', 'suffix', 'q_ans_input_type_id', 'choice', 'required', 'status', 'order'];

    public function qas()
    {
        return $this->hasMany(Qa::class);
    }

    public function qAnsInputType()
    {
        return $this->belongsTo(QAnsInputType::class);
    }

}
