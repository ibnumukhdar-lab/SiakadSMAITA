<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeeVocab extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function week() {
        return $this->belongsTo(BeeWeek::class, 'bee_week_id');
    }
}