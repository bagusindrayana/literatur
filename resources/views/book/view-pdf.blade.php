@extends('layouts.app')

@section('content')
    <div class="w-full bg-white dark:bg-gray-700 text-black dark:text-white">
        <iframe src="{{ route('book.pdf',$book->id) }}" frameborder="0" class="w-full" style="height: 80vh;"></iframe>
    </div>
@endsection
