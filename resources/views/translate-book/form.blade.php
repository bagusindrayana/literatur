@push('scripts')
    <script>
        const useApi = @json($useApi);
        const useAI = @json($useAI);
        const usingAi = document.getElementById('usingAi');
        const usingApi = document.getElementById('usingApi');
        const prePrompt = document.getElementById('pre_prompt');
        const apiKey = document.getElementById('api_key');

        function checkInput(v) {
            if (useApi.includes(v)) {
                usingApi.classList.remove('hidden');
                prePrompt.required = true;
            } else {
                usingApi.classList.add('hidden');
                prePrompt.required = false;
            }

            if (useAI.includes(v)) {
                usingAi.classList.remove('hidden');
                apiKey.required = true;
            } else {
                usingAi.classList.add('hidden');
                apiKey.required = false;
            }
        }

        // if provider contains useApi array
        const provider = document.getElementById('provider');
        provider.addEventListener('change', (e) => {
            checkInput(e.target.value);
        });
    </script>

    @if (isset($translateBook))
        <script>
            checkInput(provider.value);
        </script>
    @endif
@endpush
<div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
    @if (isset($translateBook))
        <div class="col-span-2">
            <h2 class="font-bold text-gray-900 dark:text-white">Title : {{ $translateBook->book->title }}</h2>
        </div>
    @else
        <div class="col-span-2">
            <label for="book_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book</label>
            <select id="book_id" name="book_id"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                <option selected="">Select book</option>
                @foreach ($books as $book)
                    <option value="{{ $book->id }}" @selected(old('book_id', @$translateBook->book_id) == $book->id)>{{ $book->title }}</option>
                @endforeach
            </select>
            @error('book_id')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>
    @endif

    <div>
        <label for="from_language" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">From
            Language</label>
        <select id="from_language" name="from_language"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option selected="">Select language</option>
            @foreach ($languages as $language)
                <option value="{{ $language }}" @selected(old('from_language', @$translateBook->from_language) == $language)>{{ $language }}</option>
            @endforeach
        </select>
        @error('from_language')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="to_language" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">To
            Language</label>
        <select id="to_language" name="to_language"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option selected="">Select language</option>
            @foreach ($languages as $language)
                <option value="{{ $language }}" @selected(old('to_language', @$translateBook->to_language) == $language)>{{ $language }}</option>
            @endforeach
        </select>
        @error('to_language')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-span-2">
        <label for="provider" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Provider</label>
        <select id="provider" name="provider"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option selected="">Select provider</option>
            @foreach ($providers as $provider)
                <option value="{{ $provider }}" @selected(old('provider', @$translateBook->provider))>{{ $provider }}</option>
            @endforeach
        </select>
        @error('provider')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>

    <div class="sm:col-span-2 ">
        <div id="usingAi" class="mb-4 hidden">
            <small class="text-red-500">
                using AI for prevent literal translation and better context, describe the context of the text
                like dont translate certain words, or translate certain words in a specific way.
            </small>
            <div>
                <label for="pre_prompt"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pre-Prompt</label>

                <textarea id="pre_prompt" name="pre_prompt" rows="8"
                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                    placeholder="Your description here">{{ @$translateBook->pre_prompt }}</textarea>
                @error('pre_prompt')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mb-4 hidden" id="usingApi">
            <label for="api_key" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">API Key</label>

            <textarea id="api_key" name="api_key" rows="8"
                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                placeholder="Your API Key here">{{ @$translateBook->api_key }}</textarea>
        </div>
    </div>
</div>
