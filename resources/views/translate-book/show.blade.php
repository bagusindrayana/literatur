@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('/js/autosize.min.js') }}"></script>
    <script>
        function submitAllForm() {
            // get all form .translate-form
            const forms = document.querySelectorAll('.translate-form');
            // loop through all forms
            forms.forEach(form => {
                //check checkbox
                const checkbox = form.querySelector('input[type="checkbox"]');
                if (!checkbox.checked) {
                    return;
                }



                //serialize form data
                const formData = new FormData();
                form.querySelectorAll('input, textarea').forEach(input => {
                    formData.append(input.name, input.value);
                });
                const message = document.getElementById('message-' + formData.get('page_index'));
                const original = document.getElementById('original-' + formData.get('page_index'));
                const translate = document.getElementById('translate-' + formData.get('page_index'));
                message.innerText = 'Translating...';
                //send form data to server
                fetch(form.getAttribute('action'), {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        //update message

                        message.innerText = data.message;
                        if (data.status === 'success') {
                            original.innerText = data.original_text;
                            translate.value = data.translated_text;
                        }

                    })
                    .catch(error => {
                        message.innerText = 'Error';
                        console.error('Error:', error);
                    });
            });
        }

        const allTextareas = document.querySelectorAll('textarea');
        //autosize(allTextareas);
        allTextareas.forEach(ta => {
            autosize(ta);

            ta.addEventListener('focus', function(){
                autosize.update(ta);
            });

            autosize.update(ta);
        });
    </script>
@endpush

@section('content')
    <section
        class="block w-full text-gray-900 dark:text-white  bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 ">
        <div class="p-4 mx-auto ">
            <h2 class="mb-4 text-xl font-bold ">Translate project :
                {{ $translateBook->book->title }}
            </h2>
            <p class="mb-4">
                Translate from <span class="font-semibold">{{ $translateBook->from_language }}</span> to <span
                    class="font-semibold">{{ $translateBook->to_language }}</span> using <span
                    class="font-semibold">{{ $translateBook->provider }}</span> provider
            </p>
            <div class="w-full mb-4 overflow-x-auto">
                <form action="{{ route('translate-book.save-content', $translateBook->id) }}" class="w-full"
                    id="form-changes">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    No
                                </th>
                                <th>
                                    Title
                                </th>
                                <th style="width: 200px;">
                                    Translate?
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pages as $key => $item)
                                <tr>
                                    <td>
                                        {{ $key + 1 }}
                                    </td>
                                    <td>
                                        <div data-accordion="collapse">
                                            <h2 id="accordion-collapse-heading-{{ $key }}">
                                                <button type="button"
                                                    class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border  border-gray-200 rounded-xl focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3"
                                                    data-accordion-target="#accordion-collapse-body-{{ $key }}"
                                                    aria-expanded="false"
                                                    aria-controls="accordion-collapse-body-{{ $key }}">
                                                    <span> {{ $item['title'] }}</span>
                                                    <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 10 6">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5" />
                                                    </svg>
                                                </button>
                                            </h2>

                                            <div id="accordion-collapse-body-{{ $key }}" class="hidden">
                                                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                                                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center"
                                                        id="tab-{{ $key }}"
                                                        data-tabs-toggle="#tab-{{ $key }}-content" role="tablist">
                                                        <li class="me-2" role="presentation">
                                                            <button class="inline-block p-4 border-b-2 rounded-t-lg"
                                                                id="translated-{{ $key }}-tab"
                                                                data-tabs-target="#translated-{{ $key }}"
                                                                type="button" role="tab"
                                                                aria-controls="translated-{{ $key }}"
                                                                aria-selected="false">Translated</button>
                                                        </li>
                                                        <li class="me-2" role="presentation">
                                                            <button
                                                                class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300"
                                                                id="original-{{ $key }}-tab"
                                                                data-tabs-target="#original-{{ $key }}"
                                                                type="button" role="tab"
                                                                aria-controls="original-{{ $key }}"
                                                                aria-selected="false">Original</button>
                                                        </li>

                                                    </ul>
                                                </div>
                                                <div id="tab-{{ $key }}-content">
                                                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800"
                                                        id="translated-{{ $key }}" role="tabpanel"
                                                        aria-labelledby="translated-{{ $key }}-tab">
                                                        <textarea name="translated_text[{{ $key }}]" id="translate-{{ $key }}"
                                                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">{!! @$translatedPages[$key]['translated'] !!}</textarea>
                                                    </div>
                                                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800"
                                                        id="original-{{ $key }}" role="tabpanel"
                                                        aria-labelledby="original-{{ $key }}-tab">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400"
                                                            id="original-{{ $key }}">
                                                            {!! @$translatedPages[$key]['original'] !!}
                                                        </p>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                    <td>
                                        <div action="{{ route('translate-book.translate-content', [$translateBook->id, $key, $item['href']]) }}"
                                            class="translate-form">
                                            @csrf
                                            <input type="hidden" name="page_index" value="{{ $key }}">
                                            <label class="inline-flex items-center mb-5 cursor-pointer">
                                                <input type="checkbox" checked value=""
                                                    name="translate[{{ $key }}]" class="sr-only peer">
                                                <div
                                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                                </div>
                                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300"
                                                    id="message-{{ $key }}">{{ @$translatedPages[$key]['last_status'] ?? 'Translate' }}</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="w-full flex justify-between">
                <button type="button" onclick="document.getElementById('form-changes').submit()"
                    class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-success-700 rounded-lg focus:ring-4 focus:ring-success-200 dark:focus:ring-success-900 hover:bg-success-800">
                    <svg class="flex-shrink-0 w-6 h-6 " xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M21 7v12q0 .825-.587 1.413T19 21H5q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h12zm-9 11q1.25 0 2.125-.875T15 15t-.875-2.125T12 12t-2.125.875T9 15t.875 2.125T12 18m-6-8h9V6H6z" />
                    </svg> Save Changes
                </button>
                <button type="button" onclick="submitAllForm()"
                    class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-800">
                    <span class="i-mdi-translate flex-shrink-0 w-6 h-6 "></span> Translate Pages
                </button>
            </div>
        </div>
    </section>
@endsection
