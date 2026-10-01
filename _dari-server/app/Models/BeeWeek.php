<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeeWeek extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function vocabs() {
        return $this->hasMany(BeeVocab::class, 'bee_week_id');
    }
}