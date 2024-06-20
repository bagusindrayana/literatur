<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $search = request()->query('search');
        if ($search) {
            $books = Book::where('title', 'LIKE', "%{$search}%")->get();
        } else {
            $books = Book::orderBy('created_at', 'desc')->get();
        }
        return view('home', compact('books'));
    }
}
