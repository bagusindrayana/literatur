<?php

namespace App\Http\Controllers;

use App\Jobs\TranslateGeminiAi;
use App\Models\Book;
use App\Models\TranslateBook;
use App\Models\TranslatePage;
use Illuminate\Http\Request;
use PhpZip\ZipFile;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Support\Facades\Log;
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $search = request()->query('search');
        $translateBooks = TranslateBook::with('book')->withCount('translatePages')->orderBy('created_at', 'desc')->paginate(10);
        return view('translate-book.index', compact('translateBooks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $books = Book::all();
        $languages = [
            "Japanese",
            "English",
            "Indonesian",
        ];
        $providers = [
            "Google",
            "Microsoft",
            "Yandex",
            "Gemini AI"
        ];

        $useApi = [
            "Gemini AI",
            "Yandex",
        ];

        $useAI = [
            "Gemini AI"
        ];

        return view('translate-book.create', compact('books', 'languages', 'providers', 'useApi','useAI'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required',
            'from_language' => 'required',
            'to_language' => 'required',
            'provider' => 'required',
            // 'pre_prompt' => 'required',
            // 'api_key' => 'required',
        ]);


        DB::beginTransaction();
        try {
            $translateBook = TranslateBook::create([
                'book_id' => $request->book_id,
                'from_language' => $request->from_language,
                'to_language' => $request->to_language,
                'provider' => $request->provider,
                'pre_prompt' => $request->pre_prompt,
                'api_key' => $request->api_key
            ]);
    
            $zipFile = new ZipFile();
            $zipFile->openFile($translateBook->book->path);
            $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
            $xml = simplexml_load_string($metaInfo);
            $rootfile = $xml->rootfiles->rootfile;
            $fullPath = $rootfile['full-path'];
            $content = $zipFile->getEntryContents($fullPath);
            $xml = simplexml_load_string($content);
            $json = json_encode($xml);
            $metaData = json_decode($json, true);
            $rootDir = dirname($fullPath);
    
            $navId = '';
    
    
            foreach ($metaData['manifest']['item'] as $meta) {
                if (isset($meta['@attributes']['properties']) && $meta['@attributes']['properties'] == 'nav') {
                    $navId = $meta['@attributes']['href'];
                    break;
                }
            }
    
            $dirNav = dirname($navId);
            if ($dirNav == '.') {
                $dirNav = '';
            }
    
    
            $content = $zipFile->getEntryContents($rootDir . "/" . $navId);
            $xml = simplexml_load_string($content);
            $list = $xml->body->nav->ol->li;
            
            $index = 0;
            foreach ($list as $li) {
                $a = $li->a;
                $href = $a->attributes()['href'];
                $href = explode('#', $href)[0];
                if ($dirNav != "") {
                    $path = $rootDir . "/" . $dirNav . "/" . $href;
                } else {
                    $path = $rootDir . "/" . $href;
                }
    
                $content = $zipFile->getEntryContents($path);
                $xml = simplexml_load_string($content);
                //get body
                $body = $xml->body;
                //get content
                $content = $body->asXML();
                //add new line every paragraph tag
                $content = preg_replace('/<\/p>/', "</p>\n\n", $content);
                //remove html tag
                $content = strip_tags($content);
                //remove double whitespace
                $content = preg_replace('/\s+/', ' ', $content);
                //double new line
                $content = str_replace("\n", "\n\n", $content);
    
                $translateBook->translatePages()->create([
                    'page_index' => $index,
                    'original_text' => $content,
                    'pre_prompt' => $translateBook->pre_prompt
                ]);
                $index += 1;
            }
    
            DB::commit();
            return redirect()->route('translate-book.show', $translateBook->id)->with('success', 'Translate Book created successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->route('translate-book.index')->with('error', 'Translate Book cannot be created.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TranslateBook $translateBook)
    {
        $zipFile = new ZipFile();
        $listFiles = $zipFile->openFile($translateBook->book->path)->getListFiles();
        $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
        $xml = simplexml_load_string($metaInfo);
        $rootfile = $xml->rootfiles->rootfile;
        $fullPath = $rootfile['full-path'];
        $content = $zipFile->getEntryContents($fullPath);
        $xml = simplexml_load_string($content);
        $json = json_encode($xml);
        $metaData = json_decode($json, true);
        $rootDir = dirname($fullPath);

        $navId = '';


        foreach ($metaData['manifest']['item'] as $meta) {
            if (isset($meta['@attributes']['properties']) && $meta['@attributes']['properties'] == 'nav') {
                $navId = $meta['@attributes']['href'];
                break;
            }
        }

        $dirNav = dirname($navId);
        if ($dirNav == '.') {
            $dirNav = '';
        }


        $content = $zipFile->getEntryContents($rootDir . "/" . $navId);
        $xml = simplexml_load_string($content);
        $lis = $xml->body->nav->ol->li;

        foreach ($lis as $li) {
            $a = $li->a;
            $href = $a->attributes()['href'];
            $href = explode('#', $href)[0];
            if ($dirNav != "") {
                $pages[] = [
                    'original_path' => $rootDir . "/" . $dirNav . "/" . $href,
                    'href' => base64_encode($rootDir . "/" . $dirNav . "/" . $href),
                    'title' => $a->__toString()
                ];
            } else {
                $pages[] = [
                    'original_path' => $rootDir . "/" . $href,
                    'href' => base64_encode($rootDir . "/" . $href),
                    'title' => $a->__toString()

                ];
            }

        }


       

        $translatePages = TranslatePage::where('translate_book_id', $translateBook->id)->get();
        $translatedPages = [];
        foreach ($pages as $key => $page) {
            $translatedPages[$key] = [
                "original"=>implode(' ', $translatePages->where('page_index', $key)->pluck('original_text')->toArray()),
                "translated"=>implode(' ', $translatePages->where('page_index', $key)->pluck('translated_text')->toArray()),
                'last_status'=>@$translatePages->sortByDesc('id')->where('page_index', $key)->first()->last_status,
            ];
        }

        return view('translate-book.show', compact('translateBook', 'pages','translatedPages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TranslateBook $translateBook)
    {
        $books = Book::all();
        $languages = [
            "Japanese",
            "English",
            "Indonesian",
        ];
        $providers = [
            "Google",
            "Microsoft",
            "Yandex",
            "Gemini AI"
        ];

        $useApi = [
            "Gemini AI",
            "Yandex",
        ];

        $useAI = [
            "Gemini AI"
        ];

        return view('translate-book.edit', compact('books', 'languages', 'providers', 'useApi', 'translateBook','useAI'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TranslateBook $translateBook)
    {
        $request->validate([
          
            'from_language' => 'required',
            'to_language' => 'required',
            'provider' => 'required',
            // 'pre_prompt' => 'required',
            // 'api_key' => 'required',
        ]);

        $translateBook->update([
            'from_language' => $request->from_language,
            'to_language' => $request->to_language,
            'provider' => $request->provider,
            'pre_prompt' => $request->pre_prompt,
            'api_key' => $request->api_key
        ]);
        return redirect()->route('translate-book.index')->with('success', 'Translate Book updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TranslateBook $translateBook)
    {
        try {
            $translateBook->translatePages()->delete();
            $translateBook->delete();
            return redirect()->route('translate-book.index')->with('success', 'Translate Book deleted successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            return redirect()->route('translate-book.index')->with('error', 'Translate Book cannot be deleted.');
        }
    }

    public function translateContent($translateBookId, $pageIndex, $pathContent)
    {
        $translateBook = TranslateBook::with('book')->find($translateBookId);
        $zipFile = new ZipFile();
        $zipFile->openFile($translateBook->book->path);
        $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
        $xml = simplexml_load_string($metaInfo);
        $rootfile = $xml->rootfiles->rootfile;
        $fullPath = $rootfile['full-path'];
        $rootDir = dirname($fullPath);
        $file = base64_decode($pathContent);
        $content = $zipFile->getEntryContents($file);
        $xml = simplexml_load_string($content);
        //get body
        $body = $xml->body;
        //get content
        $content = $body->asXML();
        //add new line every paragraph tag
        $content = preg_replace('/<\/p>/', "</p>\n\n", $content);
        //remove html tag
        $content = strip_tags($content);
        //remove double whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        //double new line
        $content = str_replace("\n", "\n\n", $content);
        // //trim
        // $content = trim($content);

        $translatePage = $translateBook->translatePages()->updateOrCreate(
            ['page_index' => $pageIndex],
            [
                'original_text' => $content,
                'pre_prompt' => $translateBook->pre_prompt
            ]
        );
        

        if (trim($content) == '') {
            return response()->json(['message' => 'There is no content in this page.']);
        }





        //count words in content

        //         if($countWords > 70000){
//             //split
//             $content1 = implode(' ', array_slice($arrayContent, 0, 35000));
//             $content2 = implode(' ', array_slice($arrayContent, 35000));

        //             $translatePage1 = $translateBook->translatePages()->updateOrCreate(
//                 ['page_index' => $pageIndex,'page_index_part'=>0],
//                 [
//                     'original_text' => $content1,
//                     'pre_prompt' => $translateBook->pre_prompt
//                 ]
//             );

        //             $translatePage2 = $translateBook->translatePages()->updateOrCreate(
//                 ['page_index' => $pageIndex,'page_index_part'=>1],
//                 [
//                     'original_text' => $content2,
//                     'pre_prompt' => $translateBook->pre_prompt
//                 ]
//             );
//             $prePrompt = "Translate the following ".$translateBook->from_language." texts to ".$translateBook->to_language." but do not translate person names and place names literally, for 
// aditional information or context is \n ";
//         $prePrompt .= $translateBook->pre_prompt."\n the texts is : \n\n".$content1;
//             // TranslateGeminiAi::dispatch($translateBook->api_key,$content,$translateBook->pre_prompt,$translatePage->id);
//             $client = new Client($translateBook->api_key);
//             $response = $client->geminiPro()->generateContent(
//                 new TextPart($prePrompt),
//             );
//             $translatePage1->update([
//                 'translated_text' => $response->text(),
//             ]);
//             $prePrompt = "Translate the following ".$translateBook->from_language." texts to ".$translateBook->to_language." but do not translate person names and place names literally, for 
// aditional information or context is \n ";
//         $prePrompt .= $translateBook->pre_prompt."\n the texts is : \n\n".$content2;
//             // TranslateGeminiAi::dispatch($translateBook->api_key,$content,$translateBook->pre_prompt,$translatePage->id);
//             $client2 = new Client($translateBook->api_key);
//             $response2 = $client2->geminiPro()->generateContent(
//                 new TextPart($$prePrompt),
//             );
//             $translatePage2->update([
//                 'translated_text' => $response2->text(),
//             ]);

        //             return response()->json(['message' => 'Translate Page created successfully.']);
//         } else {
//             $translatePage = $translateBook->translatePages()->updateOrCreate(
//                 ['page_index' => $pageIndex],
//                 [
//                     'original_text' => $content,
//                     'pre_prompt' => $translateBook->pre_prompt
//                 ]
//             );
//         }
        // $prePrompt = "Translate the following " . $translateBook->from_language . " texts to " . $translateBook->to_language . " but do not translate person names and place names literally, for aditional information or context is : \n ";
        // $prePrompt .= $translateBook->pre_prompt . ", \n the texts is : \n\n\n" . $content;
        $results = [];
        try {



            // $client = new Client($translateBook->api_key);
            // $response = $client->geminiPro()->countTokens(
            //     new TextPart($prePrompt),
            // );

            // if ($response->totalTokens >= 10000) {
            if($translateBook->from_language == 'Japanese'){
                Jieba::init();
                Finalseg::init();

                $arrayContent = Jieba::cut($content);
                $countWords = count($arrayContent);
            } else {
                $arrayContent = explode(' ', $content);
                $countWords = count($arrayContent);
            }
            if($countWords > 1000){
                //split into 2
                // $arrayContent = explode(' ', $content);
                // $countWords = count($arrayContent);
                $contents = [];
                $content1 = implode(' ', array_slice($arrayContent, 0,(int)$countWords / 2));
                $content2 = implode(' ', array_slice($arrayContent, (int)$countWords / 2));
                $contents = [$content1, $content2];

                foreach ($contents as $key => $c) {
                    $translatePage = $translateBook->translatePages()->updateOrCreate(
                        ['page_index' => $pageIndex, 'page_index_part' => $key],
                        [
                            'original_text' => $c,
                            'pre_prompt' => $translateBook->pre_prompt
                        ]
                    );
                    
                    // $response = $client->geminiPro()->countTokens(
                    //     new TextPart($prePrompt),
                    // );
                    // return $response->totalTokens;
                    if($translateBook->provider == 'Google'){
                        $results[] = $this->translateGoogle($translateBook, $translatePage);
                    } else {
                        $results[] = $this->translateGeminiApi($translateBook, $translatePage);
                    }
                }


            } else {
                if($translateBook->provider == 'Google'){
                    $results[] = $this->translateGoogle($translateBook, $translatePage);
                } else {
                    $results[] = $this->translateGeminiApi($translateBook, $translatePage);
                }
            }

            // TranslateGeminiAi::dispatch($translateBook->api_key,$content,$translateBook->pre_prompt,$translatePage->id);

            //$this->translateGeminiApi($translateBook->api_key, $prePrompt, $translatePage);
            $originalTexts = "";
            $translatedTexts = "";
            foreach ($results as $result) {
                $originalTexts .= $result->original_text;
                $translatedTexts .= $result->translated_text;
            }

            return response()->json(['status'=>'success','message' => 'Translate successfully.','original_text'=>$originalTexts,'translated_text'=>$translatedTexts]);
        } catch (\Throwable $th) {
            Log::error($th);
            $translatePage->update([
                'last_status' => 'failed'
            ]);
            return response()->json(['status'=>'failed','message' => 'Translate failed.'], 500);
        }

    }

    private function translateGeminiApi(TranslateBook $translateBook, TranslatePage $translatePage)
    {   
        $prePrompt = "Translate the following " . $translateBook->from_language . " texts to " . $translateBook->to_language . " but do not translate person names and place names literally, for aditional information or context is : \n ";
        $prePrompt .= $translateBook->pre_prompt . ", \n the texts is : \n\n\n" . $translatePage->original_text;
        $client = new Client($translateBook->api_key);
        $response = $client->geminiPro()->generateContent(
            new TextPart($prePrompt),
        );
        $translatePage->update([

            'translated_text' => $response->text(),
            'last_status' => 'success'
        ]);

        return $translatePage;

    }

    private function translateGoogle(TranslateBook $translateBook, TranslatePage $translatePage)
    {   
        $tr = new GoogleTranslate();
        if($translateBook->from_language == 'Japanese'){
            $tr->setSource('ja');
        } else if($translateBook->from_language == 'English'){
            $tr->setSource('en');
        } else if($translateBook->from_language == 'Indonesian'){
            $tr->setSource('id');
        }

        if($translateBook->to_language == 'Japanese'){
            $tr->setTarget('ja');
        } else if($translateBook->to_language == 'English'){
            $tr->setTarget('en');
        } else if($translateBook->to_language == 'Indonesian'){
            $tr->setTarget('id');
        }
       
        $result = $tr->translate($translatePage->original_text);
        $translatePage->update([

            'translated_text' => $result,
            'last_status' => 'success'
        ]);


        return $translatePage;

    }

    public function saveChanges(Request $request, $id){
        return redirect()->back();
    }
}
