<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrizeDrawDay extends Model
{
    use HasFactory;

    protected $table = 'prize_draw_days';

    protected $fillable = [
        'date',
        'result_special',
        'day_label'
    ];

    public function results()
    {
        return $this->hasMany(Result::class, 'day_id');
    }
}
