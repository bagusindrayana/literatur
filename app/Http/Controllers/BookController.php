<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\TranslatePage;
use Exception;
use Illuminate\Http\Request;
use Native\Laravel\Dialog;
use Kiwilan\Ebook\Ebook;
use ZipArchive;
use PhpZip\ZipFile;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $files = Dialog::new()
                ->title('Select a file')
                ->filter('Documents', ['pdf', 'epub'])
                ->multiple()
                ->open();

            if (!$files) {
                return redirect()->back();
            }

            foreach ($files as $file) {

                $path = $file;
                $file = storage_path('app/books/' . basename($file));
                //validate pdf, epub
                if (!in_array(pathinfo($path, PATHINFO_EXTENSION), ['pdf', 'epub'])) {
                    continue;
                }
                //check directory, if not exist create it
                if (!is_dir(dirname($file))) {
                    mkdir(dirname($file), 0777, true);
                }
                //copy file to storage
                copy($path, $file);

                $ebook = Ebook::read($file);



                $book = new Book();
                $book->title = $ebook->getTitle() ?? basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
                $book->author = $ebook->getAuthorMain();
                $book->publisher = $ebook->getPublisher();
                $book->description = $ebook->getDescription();
                if ($ebook->getPublishDate()) {
                    $book->year = $ebook->getPublishDate()->format('Y');
                }

                if ($ebook->hasCover()) {
                    $cover = $ebook->getCover();
                    $book->cover = $cover->getContents(true);
                } else {
                    if (pathinfo($path, PATHINFO_EXTENSION) !== 'pdf') {
                        $zipFile = new ZipFile();
                        $zipFile->openFile($file);
                        $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
                        $xml = simplexml_load_string($metaInfo);
                        $rootfile = $xml->rootfiles->rootfile;
                        $fullPath = $rootfile['full-path'];
                        $rootDir = dirname($fullPath);
                        $content = $zipFile->getEntryContents($fullPath);
                        $xml = simplexml_load_string($content);
                        $json = json_encode($xml);
                        $metaData = json_decode($json, true);
                        $cover = null;
                        foreach ($metaData['manifest']['item'] as $meta) {
                            if (isset($meta['@attributes']['properties']) && $meta['@attributes']['properties'] == 'cover-image') {
                                $cover = $meta['@attributes']['href'];
                                break;
                            }
                        }
                        if ($cover) {
                            // $content = $zipFile->getEntryContents($rootDir . "/" . $cover);

                            //$book->cover = 'data:image/'.pathinfo($cover, PATHINFO_EXTENSION).';base64,'.base64_encode($content);
                            $book->cover = base64_encode($rootDir . "/" . $cover);

                        }
                    }
                }
                $book->path = $file;
                $book->save();

            }
            return redirect()->back();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $files = $request->file('files');

            if (!$files) {
                return redirect()->back();
            }

            foreach ($files as $file) {

                $originalName = $file->getClientOriginalName();
                $originalExtension = $file->getClientOriginalExtension();
                $newName = str_replace('.' . $originalExtension, '', $originalName) . '-' . time() . '.' . $originalExtension;

                $file = $file->storeAs('books', $newName);

                $path = $file;
                //validate pdf, epub
                if (!in_array(pathinfo($path, PATHINFO_EXTENSION), ['pdf', 'epub'])) {
                    continue;
                }

                $file = storage_path('app/' . $file);
                $ebook = Ebook::read($file);


                $book = new Book();
                $book->title = $ebook->getTitle() ?? basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
                $book->author = $ebook->getAuthorMain();
                $book->publisher = $ebook->getPublisher();
                $book->description = $ebook->getDescription();
                if ($ebook->getPublishDate()) {
                    $book->year = $ebook->getPublishDate()->format('Y');
                }

                if ($ebook->hasCover()) {
                    $cover = $ebook->getCover();
                    $book->cover = $cover->getContents(true);
                } else {
                    if (pathinfo($path, PATHINFO_EXTENSION) !== 'pdf') {
                        $zipFile = new ZipFile();
                        $zipFile->openFile($file);
                        $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
                        $xml = simplexml_load_string($metaInfo);
                        $rootfile = $xml->rootfiles->rootfile;
                        $fullPath = $rootfile['full-path'];
                        $rootDir = dirname($fullPath);
                        $content = $zipFile->getEntryContents($fullPath);
                        $xml = simplexml_load_string($content);
                        $json = json_encode($xml);
                        $metaData = json_decode($json, true);
                        $cover = null;
                        foreach ($metaData['manifest']['item'] as $meta) {
                            if (isset($meta['@attributes']['properties']) && $meta['@attributes']['properties'] == 'cover-image') {
                                $cover = $meta['@attributes']['href'];
                                break;
                            }
                        }
                        if ($cover) {
                            // $content = $zipFile->getEntryContents($rootDir . "/" . $cover);

                            //$book->cover = 'data:image/'.pathinfo($cover, PATHINFO_EXTENSION).';base64,'.base64_encode($content);
                            $book->cover = base64_encode($rootDir . "/" . $cover);

                        }
                    }
                }
                $book->path = $file;
                $book->save();

            }
            return redirect()->back();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    //find nested array key
    private function array_search($array, $key, $value)
    {
        foreach ($array as $k => $val) {
            if ($val[$key] == $value) {
                return $k;
            }
        }
        return null;
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {

        if (pathinfo($book->path, PATHINFO_EXTENSION) == 'pdf') {
            // $config = new Config();
            // $config->setIgnoreEncryption(true);
            // $parser = new Parser([], $config);
            
            // try {

            //     $pdf = $parser->parseFile($book->path);
            //     dd($pdf->getDictionary()['Catalog']['all']);
            // } catch (Exception $e) {
            //     dd($e);
            // }


            return view('book.view-pdf', compact('book'));
        }
        //dd(phpinfo());
        // $ebook = Ebook::read($book->path);
        // $epub = $ebook->getParser()?->getEpub();

        // $zipArchive = new ZipArchive;
        // $zipArchive->open($book->path);

        $zipFile = new ZipFile();
        $listFiles = $zipFile->openFile($book->path)->getListFiles();
        $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
        $xml = simplexml_load_string($metaInfo);
        $rootfile = $xml->rootfiles->rootfile;
        $fullPath = $rootfile['full-path'];
        $content = $zipFile->getEntryContents($fullPath);
        $xml = simplexml_load_string($content);
        $json = json_encode($xml);
        $metaData = json_decode($json, true);
        $rootDir = dirname($fullPath);

        $images = [];
        $pages = [];
        // foreach ($listFiles as $file) {
        //     // //if file is html
        //     // if (pathinfo($file, PATHINFO_EXTENSION) == 'xhtml') {
        //     //     $content = $zipFile->getEntryContents($file);
        //     //     dd($content);
        //     //     //do something with content
        //     // }

        //     //if find opf file parse opf as xml and convert to json
        //     if (pathinfo($file, PATHINFO_EXTENSION) == 'opf') {
        //         $content = $zipFile->getEntryContents($file);
        //         $xml = simplexml_load_string($content);
        //         $json = json_encode($xml);
        //         $metaData = json_decode($json, true);
        //         break;
        //     }


        //     //get all image and convert to base64
        //     if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
        //         // $content = $zipFile->getEntryContents($file);
        //         // $base64Image[$file] = 'data:image/'.pathinfo($file, PATHINFO_EXTENSION).';base64,'.base64_encode($content);
        //         $images[] = base64_encode($file);
        //     }

        //     // if (pathinfo($file, PATHINFO_EXTENSION) == 'ncx') {
        //     //     $content = $zipFile->getEntryContents($file);
        //     //     $xml = simplexml_load_string($content);
        //     //     $json = json_encode($xml);
        //     //     $jsonData = json_decode($json, true);
        //     //     $navMap = $jsonData['navMap']['navPoint'];

        //     //     foreach ($navMap as $nav) {
        //     //         $pages[] = base64_encode($rootDir."/".$nav['content']['@attributes']['src']);
        //     //     }
        //     //     break;
        //     // }


        // }

        //find properties="nav" in metadata
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
                    'href' => base64_encode($rootDir . "/" . $dirNav . "/" . $href),
                    'title' => $a->__toString()
                ];
            } else {
                $pages[] = [
                    'href' => base64_encode($rootDir . "/" . $href),
                    'title' => $a->__toString()

                ];
            }

        }

        $lastReading = [];
        if ($book->last_reading_at) {
            $lastReading = explode(":", $book->last_reading_at);
        }

        return view('book.view', compact('book', 'pages', 'lastReading'));
    }

    public function content($bookId, $pathContent)
    {
        $book = Book::find($bookId);
        if (request()->chapter != null && request()->index != null) {
            $book->last_reading_at = request()->chapter . ":" . request()->index;
            $book->save();
        }
        $zipFile = new ZipFile();
        $zipFile->openFile($book->path);
        $metaInfo = $zipFile->getEntryContents('META-INF/container.xml');
        $xml = simplexml_load_string($metaInfo);
        $rootfile = $xml->rootfiles->rootfile;
        $fullPath = $rootfile['full-path'];
        $rootDir = dirname($fullPath);
        $file = base64_decode($pathContent);
        // $content = $zipFile->getEntryContents($rootDir.'/'.base64_decode($pathContent));
        $content = $zipFile->getEntryContents($file);

        //if file is html
        if (pathinfo($file, PATHINFO_EXTENSION) == 'xhtml') {
            //find image tag and replace src with route('book.content',[$book->id,base64_encode($image)])
            $content = preg_replace_callback('/<img[^>]+>/i', function ($matches) use ($book, $rootDir) {
                $img = $matches[0];
                $src = '';
                preg_match('/src="([^"]*)"/i', $img, $src);
                $src = str_replace('../', $rootDir . '/', $src[1]);
                $src = base64_encode($src);
                // $img = str_replace('src="'.$src.'"', 'src="'.route('book.content',[$book->id,$src]).'"', $img);
                //replace all value inside src attribute
                $img = preg_replace('/src="([^"]*)"/i', 'src="' . route('book.content', [$book->id, $src]) . '"', $img);
                return $img;
            }, $content);

            //find link style
            $content = preg_replace_callback('/<link[^>]+>/i', function ($matches) use ($book, $rootDir) {
                $link = $matches[0];
                $href = '';
                preg_match('/href="([^"]*)"/i', $link, $href);
                $href = str_replace('../', $rootDir . '/', $href[1]);
                $href = base64_encode($href);
                // $link = str_replace('href="'.$href.'"', 'href="'.route('book.content',[$book->id,$href]).'"', $link);
                //replace all value inside href attribute
                $link = preg_replace('/href="([^"]*)"/i', 'href="' . route('book.content', [$book->id, $href]) . '"', $link);
                return $link;
            }, $content);

            //only get content inside body tag
            // $content = preg_replace('/.*<body[^>]*>|<\/body>.*/si', '', $content);
            // dd($content);

            //remove new line
            $content = preg_replace('/\s+/', ' ', $content);

            return response($content)->header('Content-Type', 'application/xhtml+xml');
        }
        //if image
        else if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
            return response($content)->header('Content-Type', 'image/' . pathinfo(base64_decode($pathContent), PATHINFO_EXTENSION));
        } else {
            return response($content)->header('Content-Type', 'application/octet-stream');
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        try {
            //check if exist
            if (file_exists($book->path)) {
                unlink($book->path);
            }
            TranslatePage::whereHas('translateBook', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })->delete();
            $book->translateBooks()->delete();
            $book->delete();
            return redirect()->back();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    //download
    public function download($bookId)
    {
        $book = Book::find($bookId);
        return response()->download($book->path);
    }

    //pdf
    public function pdf($bookId)
    {
        $book = Book::find($bookId);
        return response()->file($book->path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $book->title . '.pdf"'
        ]);
    }
}
