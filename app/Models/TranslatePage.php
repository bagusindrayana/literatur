<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslatePage extends Model
{
    use HasFactory;
    protected $fillable = [
        'translate_book_id',
        'page_index',
        'original_text',
        'translated_text',
        'pre_prompt',
        'page_index_part',
        'last_status'
    ];

    public function translateBook()
    {
        return $this->belongsTo(TranslateBook::class);
    }
}
