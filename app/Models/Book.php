<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'year',
        'pages',
        'language',
        'isbn',
        'cover',
        'description',
        'last_reading_at',
        'path'
    ];

    public function translateBooks()
    {
        return $this->hasMany(TranslateBook::class);
    }
}
