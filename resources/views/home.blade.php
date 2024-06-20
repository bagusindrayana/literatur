@extends('layouts.app')

@push('styles')
    <style>
        .file-area {
            position: relative;
        }
        .file-area input[type=file] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-area .file-dummy {
            width: 100%;
            padding: 50px 30px;
            border: 2px dashed #ccc;
            background-color: #fff;
            text-align: center;
            transition: background 0.3s ease-in-out;
        }

        .file-area .file-dummy .success {
            display: none;
        }

        .file-area:hover .file-dummy {
            border: 2px dashed #1abc9c;
        }

        .file-area input[type=file]:valid+.file-dummy {
            border-color: #1abc9c;
        }

        .file-area input[type=file]:valid+.file-dummy .success {
            display: inline-block;
        }

        .file-area input[type=file]:valid+.file-dummy .default {
            display: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const dropContainer = document.getElementById('drop-container');
        const fileInput = document.getElementById('dropzone-file');
        // dropContainer.ondragover = dropContainer.ondragenter = function(evt) {
        //     evt.preventDefault();
        // };

        // dropContainer.ondrop = function(evt) {
        //     // pretty simple -- but not for IE :(
        //     fileInput.files = evt.dataTransfer.files;

        //     // If you want to use some of the dropped files
        //     const dT = new DataTransfer();
        //     dT.items.add(evt.dataTransfer.files[0]);
        //     dT.items.add(evt.dataTransfer.files[3]);
        //     fileInput.files = dT.files;

        //     evt.preventDefault();
        // };

        fileInput.addEventListener('change', (e) => {
            document.getElementById('upload-form').submit();
            document.getElementById('loading-upload').classList.remove('hidden');
            document.getElementById('upload-form').classList.add('hidden');
        });
    </script>
@endpush

@section('content')
    <div class="w-full mb-4">
        <form action="" method="GET">
            <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="search" name="search" id="default-search"
                    class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="Search book title..." value="{{ request()->search }}" />
                <button type="submit"
                    class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Search</button>
            </div>
        </form>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-4 ">


        <label for="dropzone-file" id="drop-container"
            class="file-area text-center flex flex-col items-center justify-center w-full min-h-80 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
            <div role="status " id="loading-upload" class="hidden">
                <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                    viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                        fill="currentColor" />
                    <path
                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                        fill="currentFill" />
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
            <form action="{{ route('book.store') }}" method="POST" id="upload-form" enctype="multipart/form-data">
                @csrf
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <span class="i-mdi-plus text-8xl mb-4 text-gray-500 dark:text-gray-400"></span>

                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to
                            upload</span> or drag and drop</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">(PDF, EPUB)</p>
                </div>
                {{-- only accept pdf and epub --}}
                <input id="dropzone-file" multiple name="files[]" type="file" accept=".pdf,.epub" />
            </form>
        </label>


        {{-- <a href="{{ route('book.create') }}"
            class="flex justify-center items-center border-2 border-dashed border-gray-300 rounded-lg dark:border-gray-600 min-h-80">
           
                <span class="i-mdi-plus text-white text-8xl"></span>
            
        </a> --}}
        @foreach ($books as $book)
            <div
                class="max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col justify-between">
                @if ($book->cover)
                    <img src="{{ route('book.content', [$book->id, $book->cover]) }}" alt="{{ $book->title }}"
                        class="object-cover w-full h-60 rounded-t-lg" />
                @else
                    <img class="rounded-t-lg object-cover w-full  h-60" src="{{ asset('images/book_cover.png') }}"
                        alt="" />
                @endif
                <div class="p-2">
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ $book->title }}</p>

                    <div class="w-full flex flex-col gap-1 md:flex-row justify-between">
                        <a href="{{ route('book.show', $book->id) }}"
                            class="inline-flex w-full md:w-auto items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Read
                            <span class="i-mdi-book w-3.5 h-3.5 ms-2"></span>

                        </a>
                        <form action="{{ route('book.destroy', $book->id) }}" method="POST" class="w-full  md:w-auto">
                            @csrf
                            @method('DELETE')
                            <button
                                class="inline-flex w-full  md:w-auto items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                Delete
                                <span class="i-mdi-trash w-3.5 h-3.5 ms-2"></span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection
