@extends('layouts.app')

@push('styles')
    <style>
        #container {
         
            height: 70vh;
            overflow: hidden;
            border: 1px solid #000;
            padding: 10px;
        }

        #controls {
            text-align: center;
            /* margin-top: 10px; */
        }

        .page {
            display: none;
        }


        #container section {
            width: auto !important;
            height: 100% !important;

        }

        #container img {
            width: auto !important;
            height: 100% !important;
            object-fit: cover;
            margin: auto;
            display: block;
        }

        h1 {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
        }

        p {
            font-size: 1em;
            text-align: justify;
            padding-left: 10px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const _pages = @json($pages);
        let _pageIndex = {{$lastReading[0] ?? 0}};
        const pagesSelect = document.getElementById('pages');
        _pages.forEach((page, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = page.title;
            if(_pageIndex == index){
                option.selected = true;
            }
            pagesSelect.appendChild(option);
        });




        let contentPages = [];
        let currentPage = [];

        _pages.forEach((page, index) => {
            contentPages.push([]);
            currentPage.push(0);
        });

        currentPage[_pageIndex] = {{@$lastReading[1] ?? 0}};

        pagesSelect.addEventListener('change', (e) => {

            // currentPage[_pageIndex] = contentPages[_pageIndex].length - 1;
            const v = _pageIndex;
            _pageIndex = parseInt(e.target.value);
            if (v < parseInt(e.target.value)) {
                getContent(true);
            } else {
                getContent(false);
            }


        });




        const container = document.getElementById('container');
        const pageNumSpan = document.getElementById('page-num');
        const prevButton = document.getElementById('prev');
        const nextButton = document.getElementById('next');

        let curText = "...";

        function getContent(next = false) {
            const href = _pages[_pageIndex]['href'];
            const url = `{{ route('book.content', [$book->id, '']) }}/${href}?chapter=${_pageIndex}&index=${currentPage[_pageIndex]}`;
            //request content
            fetch(url)
                .then(response => response.text())
                .then(data => {
                    // if (curText != "...") {
                    //     currentPage[_pageIndex]++;
                    // }
                    if (data.includes('html')) {
                        //parse html
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(data, 'text/html');
                        //cont child nodes
                        // const elements = Array.from(doc.body.children[0].children);
                        // console.log(elements);
                        curText = doc.body.innerHTML;

                    } else {
                        curText = data;
                    }


                    // text = data;

                    createPages(curText, container);
                    displayPage(currentPage[_pageIndex]);
                    updateControls();

                    if (next) {

                        if (parseInt(_pageIndex) > 0) {
                            for (let i = 0; i < parseInt(_pageIndex); i++) {
                                currentPage[i] = contentPages[i].length > 0 ? contentPages[i].length - 1 : -1;
                            }
                            // currentPage[parseInt(_pageIndex) - 1] = -1;
                            // console.log(parseInt(_pageIndex) - 1);
                        }


                    } else {
                        if (parseInt(_pageIndex) < _pages.length && currentPage[parseInt(_pageIndex) + 1] < contentPages[parseInt(
                                _pageIndex) + 1].length - 1) {
                            for (let i = parseInt(_pageIndex) + 1; i < _pages.length; i++) {
                                currentPage[i] = 0;
                            }
                        }
                    }
                });

        }

        getContent();



        // function createPages(text, container) {
        //     const containerHeight = container.clientHeight;
        //     const words = text.split(' ');

        //     let pageText = '';
        //     let tempDiv = document.createElement('div');
        //     tempDiv.style.visibility = 'hidden';
        //     tempDiv.style.position = 'absolute';
        //     tempDiv.style.width = container.clientWidth + 'px';
        //     document.body.appendChild(tempDiv);

        //     words.forEach(word => {
        //         tempDiv.innerHTML = pageText + ' ' + word;
        //         if (tempDiv.clientHeight > containerHeight) {
        //             pages.push(pageText.trim());
        //             pageText = word;
        //         } else {
        //             pageText += ' ' + word;
        //         }
        //     });

        //     if (pageText.trim().length > 0) {
        //         pages.push(pageText.trim());
        //     }

        //     document.body.removeChild(tempDiv);
        // }

        function deepList(listNodes) {
            let nodes = [];
            listNodes.forEach(node => {

                if (node.childNodes.length > 0) {
                    nodes = nodes.concat(deepList(node.childNodes));
                } else {
                    //if node is string
                    if (node.nodeType === 3) {
                        const text = node.textContent.trim();
                        if (text.length > 0) {
                            const span = document.createElement(node.parentElement.nodeName);
                            span.textContent = text;
                            nodes.push(span);

                        }
                    } else {
                        nodes.push(node);
                    }


                }
            });
            return nodes;
        }

        function createPages(text, container) {
            const containerHeight = container.clientHeight;

            const tempDiv = document.createElement('div');
            tempDiv.style.visibility = 'hidden';
            tempDiv.style.position = 'absolute';
            tempDiv.style.width = container.clientWidth + 'px';
            document.body.appendChild(tempDiv);

            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');

            // const elements = Array.from(doc.body.childNodes);

            const elements = deepList(doc.body.childNodes);
            // console.log(elements);
            // for (let i = 0; i < elements.length; i++) {
            //     const element = elements[i];

            // }

            let pageText = '';
            tempDiv.innerHTML = '';



            elements.forEach(element => {
                tempDiv.appendChild(element.cloneNode(true));

                if (tempDiv.clientHeight > containerHeight) {

                    contentPages[_pageIndex].push(pageText);
                    tempDiv.innerHTML = '';
                    tempDiv.appendChild(element.cloneNode(true));

                    pageText = element.outerHTML;
                } else {
                    pageText += element.outerHTML;
                }


            });


            if (pageText && pageText.trim().length > 0) {
                contentPages[_pageIndex].push(pageText);
            }

            if(currentPage[_pageIndex] == -1){
                console.log("LAST");
                currentPage[_pageIndex] = contentPages[_pageIndex].length - 1;
            }



            document.body.removeChild(tempDiv);
        }

        function displayPage(pageNum) {
            container.innerHTML = contentPages[_pageIndex][pageNum];
            //pageNumSpan.textContent = `${pageNum + 1} / ${pages.length}`;
        }

        function updateControls() {
            // prevButton.disabled = currentPage[_pageIndex] === 0;
            nextButton.disabled = _pageIndex === _pages.length - 1;
        }

        prevButton.addEventListener('click', () => {
            if (currentPage[_pageIndex] > 0) {
                currentPage[_pageIndex]--;

            } else if (_pageIndex > 0 && currentPage[_pageIndex] != -1) {
                _pageIndex--;


            }

            // if(currentPage[_pageIndex] < 0){
            //     currentPage[_pageIndex] = 0;
            // }

            if (contentPages[_pageIndex].length === 0) {
                getContent();
            } else {
                displayPage(currentPage[_pageIndex]);
                updateControls();
            }

            console.log(_pageIndex);
            console.log(currentPage[_pageIndex]);
        });

        nextButton.addEventListener('click', () => {

            if (currentPage[_pageIndex] < contentPages[_pageIndex].length - 1) {
                currentPage[_pageIndex]++;

            } else if (_pageIndex < _pages.length - 1) {
                _pageIndex++;


            }

            if (contentPages[_pageIndex].length === 0) {
                getContent();
            } else {
                displayPage(currentPage[_pageIndex]);
                updateControls();
            }
        });

        // createPages(text, container);
        // displayPage(currentPage);
        // updateControls();
    </script>
@endpush

@section('content')
    <div class="w-full mb-4">
        <label for="pages" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select
            chapter/section</label>
        <select id="pages"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

        </select>
    </div>
    <div class="w-full bg-white dark:bg-gray-700 text-black dark:text-white rounded-lg">

        <div id="container" class=" w-full   rounded-t-lg"></div>
        <div id="controls" class="w-full flex gap-1 justify-center py-4">
            <button id="prev"
                class="flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Previous</button>
            {{-- <span id="page-num"></span> --}}
            <button id="next"
                class="flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</button>
        </div>
    </div>
@endsection
