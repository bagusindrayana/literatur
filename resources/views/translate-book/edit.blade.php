@extends('layouts.app')

@section('content')
    <section
        class="block w-full  bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 ">
        <div class="w-full p-4">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Start new translate project</h2>
            <form action="{{ route('translate-book.update', $translateBook->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('translate-book.form')
                <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-800">
                    Save project
                </button>
            </form>
        </div>
    </section>
@endsection
