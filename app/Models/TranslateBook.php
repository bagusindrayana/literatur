<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslateBook extends Model
{
    use HasFactory;
    protected $fillable = ['book_id','from_language','to_language','provider','pre_prompt','api_key'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function translatePages()
    {
        return $this->hasMany(TranslatePage::class);
    }
}
