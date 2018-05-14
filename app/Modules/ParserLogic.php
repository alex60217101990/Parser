<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.04.2018
 * Time: 11:42
 */
namespace App\Modules;
use App\Events\ParsePusherEvent;
use App\new_ad;
use App\new_ad_image;
use App\proxy_list;
use App\proxy_test_list;
use Config;
use Dan\UploadImage\UploadImage;
use DOMDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Queue\SerializesModels;
use Pusher\Laravel\Facades\Pusher;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DomCrawler\Crawler;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Dan\UploadImage\Exceptions\UploadImageException;
use Faker\Generator as Faker;

class ParserLogic implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $keys;
    protected $userAgents;
    protected $search_fields;
    protected $proxy_list;
    protected $proxy_for_image;
    protected $last_proxy;

    protected $position;

    public function __construct(){
        $this->userAgents = [
            'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.4 (KHTML, like Gecko) Chrome/6.0.481.0 Safari/534.4',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.540.0 Safari/534.10',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/3.0.195.27 Safari/532.0',
            'Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Ubuntu/10.10 Chromium/8.0.552.237 Chrome/8.0.552.237 Safari/534.10',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.30 (KHTML, like Gecko) Ubuntu/10.10 Chromium/12.0.742.112 Chrome/12.0.742.112 Safari/534.30',
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.108 Safari/537.36 2345Explorer/7.1.0.12633',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1b3) Gecko/20090305 Firefox/3.1b3 GTB5',
            'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.8.1.12) Gecko/20080214 Firefox/2.0.0.12',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b7) Gecko/20101111 Firefox/4.0b7',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b8pre) Gecko/20101114 Firefox/4.0b8pre',
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:22.0) Gecko/20130328 Firefox/22.0',
            'Mozilla/5.0 (X11; Linux i686; rv:30.0) Gecko/20100101 Firefox/30.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:58.0) Gecko/20100101 Firefox/58.0',
            'Mozilla/5.0 (X11; Linux i686; rv:30.0) Gecko/20100101 Firefox/30.0'
        ];

        $this->search_fields = [
            'Объявление от'=>'','Выберите рубрику'=>'','Готов сотрудничать с риэлторами'=>'',
            'Тип дома'=>'','Этаж'=>'','Общая площадь'=>'','Этажность'=>'','Площадь кухни'=>'',
            'Без комиссии'=>'','Расстояние до ближайшего города'=>'','Тип недвижимости'=>'',
            'Площадь участка'=>'','Постройки на участке'=>'','Тип объекта'=>'', 'Количество комнат'=>'',
            'Планировка'=>'','Санузел'=>'','Отопление'=>'','Ремонт'=>'','Бытовая техника'=>'',
            'Комфорт'=>'','Коммуникации'=>'','Инфраструктура (до 500 метров)'=>'',
            'Ландшафт (до 1 км.)'=>'','Меблирование'=>'','Мультимедиа'=>'','price'=>'',
            'message'=>'', 'address'=>'', 'ad_added'=>'','title'=>'', 'photos_src'=>[]

        ];

        $this->proxy_list = proxy_test_list::all()->sortByDesc('rating')->take(300);
        $this->proxy_for_image = $this->proxy_list->take(10);
        //$this->proxy_list = proxy_test_list::all()->sortByDesc('id');

       // $this->proxy_list = proxy_test_list::all()->sortBy('rating')->take(100);
      //  $this->proxy_for_image = proxy_test_list::all()->sortByDesc('rating')->take(10);
        //(Тип дома<.*>\s*<.*>\s*<.*>\s*(<.*>)*\s*([a-zA-Zа-яА-ЯёЁ 0-9\S]*))(.*)
        $this->last_proxy = '';

        $this->position = 1;
    }

    public function ParseCategory($region, $page_size){
        try {
            $links = [];
            array_push($links, 'https://www.olx.ua/nedvizhimost/'.$region);
            for($j=2; $j<$page_size+1; $j++) {
                array_push($links, 'https://www.olx.ua/nedvizhimost/'.$region.'?page='.$j);
            }
            $globalLinksArr = [];
          foreach ($links as $link){
           /*         $response = Curl::to($link)
                    //  ->withProxy('195.80.140.212')
                        ->withHeader('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8')
                        ->withHeader('Accept-Language: ru,en-us;q=0.7,en;q=0.3')
                        ->withHeader('Accept-Encoding: deflate')
                        ->withHeader('Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7')
                        ->withData('action=login&imembername=valenok&ipassword=ne_skaju&submit=%C2%F5%EE%E4')
                        ->get();*/
              $response = $this->getContentCURL($link);

                    // Create new instance for parser.
                    $crawler = new Crawler(null, $link);
                    $crawler->addHtmlContent($response[0], 'UTF-8');

                    /**
                     * get All main body content.
                     */
                    $mainContent = $crawler->filterXPath('//table[contains(@id, "offers_table")]')->html();

                    /**
                     * get All links.
                     */
                    $crawlerLinks = new Crawler();
                    $crawlerLinks->addHtmlContent($mainContent);
                    $links = $crawlerLinks->filter('a')->each(function (Crawler $node, $i) {
                        return $node->attr('href');
                    });
              //    $LinksArr = [];
                    if (!empty($links)) {
                        for ($i = 1; $i < count($links); $i++) {
                            if ($links[$i] != '#') {
                             //   array_push($LinksArr, $links[$i]);
                                $links[$i] = preg_replace('/([a-zA-Z0-9.:?!\/-]+)#\w+/','$1', $links[$i]);
                                array_push($globalLinksArr, $links[$i]);
                            }
                        }
                        //return array_unique($LinksArr);
                    } //else return null;
                }return array_unique($globalLinksArr);
        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }

    public function startParse($links=''){
        try {
        //    if (!empty($links) && is_array($links)) {
       //     foreach ($links as $link) {

              /*  $filePath = storage_path('app/public/testing');
                $dir = new Filesystem;
                if(!$dir->exists($filePath)){
                    $dir->makeDirectory($filePath);  //follow the declaration to see the complete signature
                }*/

             //   $response = Curl::to(/*$link*/'https://www.olx.ua/obyavlenie/prodam-3-iz-kvartiru-saltovka-IDz4Yvy.html')
                   // ->withProxy('91.237.150.68')
              /*      ->withHeader('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8')
                    ->withHeader('Accept-Language: ru,en-us;q=0.7,en;q=0.3')
                    ->withHeader('Accept-Encoding: deflate')
                    ->withHeader('Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7')
                    ->withData('action=login&imembername=valenok&ipassword=ne_skaju&submit=%C2%F5%EE%E4')
                 //   ->setCookieJar($filePath.'/Cookie.txt')
                    ->get();*/
                //$response = $this->getContentCURL('https://www.olx.ua/obyavlenie/prodam-3-iz-kvartiru-saltovka-IDz4Yvy.html');
                //  $link = 'https://www.olx.ua/obyavlenie/prodam-3-iz-kvartiru-saltovka-IDz4Yvy.html';
            $response = $this->getContentCURL('https://www.olx.ua/obyavlenie/dacha-chernvts-IDtbZ72.html');
            $link = 'https://www.olx.ua/obyavlenie/dacha-chernvts-IDtbZ72.html';

               // $rez = [];
                $PHPSESSID = '';
                foreach ($response[2] as $value) {
                    if (!empty($this->getCookies($value)) && !empty($this->getCookies($value)['PHPSESSID']))
                        //array_push($rez, $this->getCookies($value));
                        $PHPSESSID = $this->getCookies($value)['PHPSESSID'];
                }

                // Create new instance for parser.
                $crawler = new Crawler(null, $link);
                $crawler->addHtmlContent($response[0], 'UTF-8');
          //      return $response;

            /**
             * get All main body content.
             */
            $ad = new_ad::create(['link'=>$link, 'title_ad'=>'', 'body_ad'=>'',
                'price'=>'', 'ad_from'=>'', 'category'=>'',
                'coop_with_realtors'=>'', 'object_type'=>'',
                'floor'=>0, 'num_of_storeys'=>0, 'total_area'=>0.0,
                'kitchen_area'=>0.0, 'number_of_rooms'=>0, 'layout'=>'',
                'bathroom'=>'', 'heating'=>'', 'repairs'=>'',
                'appliances'=>'', 'comfort'=>'','communications'=>'',
                'infrastructure'=>'', 'landscape'=>'','photo_counter'=>0,
                'telephones'=>'', 'ad_added'=>'', 'house_type'=>'',
                'commission'=>'','dist_to_the_near_city'=>'',
                'property_type'=>'', 'land_area'=>'', 'buildings_on_plot'=>'',
                'furnishing'=>'', 'multimedia'=>''
            ]);

                try {
                    $mainDiv = $crawler->filterXPath('//section[contains(@id, "body-container")]')->html();

                    $pt = '';
                    $_pattern_search_pt = '/var phoneToken = \'([a-z0-9]+)\';/is';
                    if (preg_match($_pattern_search_pt, $mainDiv, $matches)) {
                        $pt = $matches[1];
                    }
                    $idPhone = [];
                    $_pattern_search_id_phone = '(\'id\':\'[A-Za-z0-9]+\')';
                    if (preg_match($_pattern_search_id_phone, $response[0], $matches)) {
                        $idPhone = $matches;
                    }
                    /**
                     * get All content.
                     */
                    $crawlerSection = new Crawler();
                    $crawlerSection->addHtmlContent($mainDiv);
                    $tableContent = $crawlerSection->filterXPath('//div[contains(@id, "offerdescription")]')->html();
                    /**
                     * get tittle of ad.
                     */
                    $crawlerTable = new Crawler();
                    $crawlerTable->addHtmlContent($tableContent);
                    $titleTableContent = $crawlerTable->filterXPath('//div[contains(@class, "offer-titlebox")]')->html();

                    /* <h2> */
                    $crawlerTableTitle = new Crawler();
                    $crawlerTableTitle->addHtmlContent($titleTableContent);
                    $titleTableH2Content = $crawlerTableTitle->filter('h1')->text();
                    /* <p> */
                    $crawlerTableTitleBody = new Crawler();
                    $crawlerTableTitleBody->addHtmlContent($titleTableContent);
                //    $titleTablePContent = $crawlerTableTitleBody->filterXPath('//div[contains(@class, "offer-titlebox__details")]')->text();
                    // search result.
                //    $titleTableContent = preg_replace("/\s{2,}/", " ", $titleTableH2Content . $titleTablePContent);

                    $titleTablePContentHTML = $crawlerTableTitleBody->filterXPath('//div[contains(@class, "offer-titlebox__details")]')->html();
                    $crawlerTitleAd = new Crawler();
                    $crawlerTitleAd->addHtmlContent($titleTablePContentHTML);
                    $emText = $crawlerTitleAd->filter('em')->text();
                    $emText = preg_replace("/\s{2,}/", " ", $emText);

                  //  $crawlerTableBody->filterXPath('//table[contains(@class, "details fixed marginbott20 margintop5 full")]')
                    $aText = $crawlerTitleAd->filter('strong')->text();
                    $titleTableContent = preg_replace("/\s{2,}/", " ", $titleTableH2Content . $aText);

                    /**
                     * get body table.
                     */
                    $crawlerTableBody = new Crawler();
                    $crawlerTableBody->addHtmlContent($tableContent);
                    $tableBody = $crawlerTableBody->filterXPath('//table[contains(@class, "details fixed marginbott20 margintop5 full")]')->html();
                    $crawlerTableBodyStrong = new Crawler();
                    $crawlerTableBodyStrong->addHtmlContent($tableBody);
                    $trTableContent = $crawlerTableBodyStrong->filter('td')->each(function (Crawler $node, $i) {
                        return $node->text();
                    });

                    $newArr = [];
                    foreach ($trTableContent as $parseTr) {
                        $part1 = preg_replace("/\s{2,}/", " ", $parseTr);
                        array_push($newArr, $part1);
                    }
                    // search result.
                    $newArr1 = []; $keys = [];
                    for ($i = 0; $i < count($newArr); $i++) {
                        if ($i % 2 != 0)
                            array_push($newArr1, $newArr[$i]);
                        else{
                            if(strstr($newArr[$i], 'Объявление от')){
                                $ad->ad_from = $keys['Объявление от'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Объявление от", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Выберите рубрику')){
                                $ad->category = $keys['Выберите рубрику'] = trim(preg_replace("/\s{2,}/", "",
                                    str_replace("Выберите рубрику", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Готов сотрудничать с риэлторами')){
                                $keys['Готов сотрудничать с риэлторами'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Готов сотрудничать с риэлторами", "", $newArr[$i])));
                                $ad->coop_with_realtors = "готов";
                            }
                            if(strstr($newArr[$i], 'Тип дома')){
                                $ad->house_type = $keys['Тип дома'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Тип дома", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Этаж ')){
                                $keys['Этаж'] = preg_replace("/\s{2,}/", " ",
                                    str_replace("Этаж ", "", $newArr[$i]));
                                $ad->floor = intval($keys['Этаж']);
                            }
                            if(strstr($newArr[$i], 'Этажность')){
                                $keys['Этажность'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Этажность", "", $newArr[$i])));
                                $ad->num_of_storeys = intval($keys['Этажность']);
                            }
                            if(strstr($newArr[$i], 'Общая площадь')){
                               $keys['Общая площадь'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Общая площадь", "", $newArr[$i])));
                                $ad->total_area = doubleval($keys['Общая площадь']);
                            }
                            if(strstr($newArr[$i], 'Площадь кухни')){
                                $keys['Площадь кухни'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Площадь кухни", "", $newArr[$i])));
                                $ad->kitchen_area = doubleval($keys['Площадь кухни']);
                            }
                        if(strstr($newArr[$i], 'Без комиссии')){
                            $ad->commission = $keys['Без комиссии'] = trim(preg_replace("/\s{2,}/", " ",
                                str_replace("Без комиссии", "", $newArr[$i])));
                        }
                        if(strstr($newArr[$i], 'Расстояние до ближайшего города')){
                            $ad->dist_to_the_near_city = $keys['Расстояние до ближайшего города'] = trim(preg_replace("/\s{2,}/", " ",
                                str_replace("Расстояние до ближайшего города", "", $newArr[$i])));
                        }
                        if(strstr($newArr[$i], 'Тип недвижимости')){
                            $ad->property_type = $keys['Тип недвижимости'] = trim(preg_replace("/\s{2,}/", " ",
                                str_replace("Тип недвижимости", "", $newArr[$i])));
                        }
                        if(strstr($newArr[$i], 'Площадь участка')){
                            $ad->land_area = $keys['Площадь участка'] = trim(preg_replace("/\s{2,}/", " ",
                                str_replace("Площадь участка", "", $newArr[$i])));
                        }
                        if(strstr($newArr[$i], 'Постройки на участке')){
                            $ad->buildings_on_plot = $keys['Постройки на участке'] = trim(preg_replace("/\s{2,}/", " ",
                                str_replace("Постройки на участке", "", $newArr[$i])));
                        }
                            if(strstr($newArr[$i], 'Тип объекта')){
                                $ad->object_type = $keys['Тип объекта'] = trim(preg_replace("/\s{2,}/", "",
                                    str_replace("Тип объекта", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Количество комнат')){
                                $keys['Количество комнат'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Количество комнат", "", $newArr[$i])));
                                $ad->number_of_rooms = intval($keys['Количество комнат']);
                            }
                            if(strstr($newArr[$i], 'Планировка')){
                                $ad->layout = $keys['Планировка'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Планировка", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Санузел')){
                                $ad->bathroom = $keys['Санузел	'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Санузел", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Отопление')){
                                $ad->heating = $keys['Отопление'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Отопление", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Ремонт')){
                                $ad->repairs = $keys['Ремонт'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Ремонт", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Бытовая техника')){
                                $ad->appliances = $keys['Бытовая техника'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Бытовая техника", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Комфорт')){
                                $ad->comfort = $keys['Комфорт'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Комфорт", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Коммуникации')){
                                $ad->communications = $keys['Коммуникации'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Коммуникации", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Инфраструктура (до 500 метров)')){
                                $ad->infrastructure = $keys['Инфраструктура (до 500 метров)'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Инфраструктура (до 500 метров)", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Ландшафт (до 1 км.)')){
                                $ad->landscape = $keys['Ландшафт (до 1 км.)'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Ландшафт (до 1 км.)", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Меблирование')){
                                $ad->furnishing = $keys['Меблирование'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Меблирование", "", $newArr[$i])));
                            }
                            if(strstr($newArr[$i], 'Мультимедиа')){
                                $ad->multimedia = $keys['Мультимедиа'] = trim(preg_replace("/\s{2,}/", " ",
                                    str_replace("Мультимедиа", "", $newArr[$i])));
                            }
                            // array_push($keys, $newArr[$i]);
                        }
                    }
                //    UnSet($newArr);

                    $crawlerAdBody = new Crawler();
                    $crawlerAdBody->addHtmlContent($tableContent);
                    $AdText = $crawlerAdBody->filterXPath('//div[contains(@id, "textContent")]')->text();
                    // search result.
                    $AdText = preg_replace("/\s{2,}/", " ", $AdText);

                    /**
                     * Get price.
                     */
                    $crawlerPrice = new Crawler();
                    $crawlerPrice->addHtmlContent($mainDiv);
                    $price = $crawlerPrice->filterXPath('//div[contains(@class, "price-label")]')->text();
                    $price = preg_replace("/(\s)+/", "$1", $price);
                    $price = preg_replace("/( )+/", "", $price);
                    trim($price);

                    /**
                     * Get telephone. ????????????????????????
                     */
                    $crawlerTel = new Crawler();
                    $crawlerTel->addHtmlContent($mainDiv);
                    $telephone = $crawlerTel->filterXPath('//strong[contains(@class, "fnormal xx-large")]')->text();
                    //$telephone =

                    /**
                     * Download all ad images.
                     */
                    $crawlerImages = new Crawler();
                    $crawlerImages->addHtmlContent($tableContent);
                    $imagesArr = $crawlerImages->filter('img')->each(function (Crawler $node, $i) {
                        return $node->attr('src');
                    });

                    $this->saveImagesFromBody($imagesArr, $ad);

                   $ad->title_ad = $titleTableContent;
                   $ad->body_ad = $AdText;
                   $ad->price = $price;
                   $ad->ad_added = $emText;
                   /* $response1 = Curl::to('https://www.olx.ua/ajax/misc/contact/phone/z4Yvy/?pt='.$pt)
                        // https://www.olx.ua/ajax/misc/contact/phone/z4Yvy
                        ->withProxy('5.135.74.36')
                    //    ->setCookieJar('C:\\log.txt')
                        ->returnResponseObject()
                        ->get();*/

                   $phoneId = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', substr($idPhone[0], 5));
                    $telResp = $this->getContentCURL("https://www.olx.ua/ajax/misc/contact/phone/".$phoneId."/",
                        $PHPSESSID, $pt);//z4Yvy/

                    $ad->telephones = $this->getTelephoneBody(implode('',$telResp[2]));
                    $ad->save();

                    return ['parse' =>/*$keys*/$phoneId, 'not parse' =>  $this->getTelephoneBody(implode('',$telResp[2])) ,
                        'token'=>$pt, 'session'=>$PHPSESSID];
                }catch (\Exception $e){
                    return $e->getTrace();
                };
          //  }

    //    }else return null;
        }catch (\Exception $exception){
            return $exception->getTrace();
        }
    }

    public function StartParsePage($links=''){
        try{
           // $links = ['https://www.olx.ua/obyavlenie/kvartira-IDz7pHv.html'];
            if (!empty($links) && is_array($links)) {
                foreach ($links as $link) {
                    $new_ad = new_ad::where('link', '=', $link)->first();
                    if(empty($new_ad)) {
                        //$link = 'https://www.olx.ua/obyavlenie/kvartira-IDz7pHv.html';
                        // $link = 'https://www.olx.ua/obyavlenie/prodatsya-budinok-smt-luzhani-85-m-kv-0-15-ga-nd-opalennya-garazh-IDzm8ZS.html';
                        //$link = 'https://www.olx.ua/obyavlenie/dacha-chernvts-IDtbZ72.html';
                        $response = $this->proxy_ip_test($link);

                        if (!empty($response) && !empty($response[0]) && !empty($response[2])) {

                            // $rez = [];
                            $PHPSESSID = '';
                            foreach ($response[2] as $value) {
                                if (!empty($this->getCookies($value)) && !empty($this->getCookies($value)['PHPSESSID']))
                                    //array_push($rez, $this->getCookies($value));
                                    $PHPSESSID = $this->getCookies($value)['PHPSESSID'];
                            }
                            /**
                             * get phone token.
                             */
                            $pt = '';
                            $_pattern_search_pt = '/var phoneToken = \'([a-z0-9]+)\';/is';
                            if (preg_match($_pattern_search_pt, $response[0], $matches)) {
                                $pt = $matches[1];
                            }
                            /**
                             * get phone uri ID.
                             */
                            $idPhone = [];
                            $_pattern_search_id_phone = '(\'id\':\'[A-Za-z0-9]+\')';
                            if (preg_match($_pattern_search_id_phone, $response[0], $matches)) {
                                $idPhone = $matches;
                            }
                            /**
                             * get all links on page.
                             */
                            preg_match_all('/(<a href="(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9]\.[^\s]{2,})")/mi',
                                $response[0], $matches, PREG_PATTERN_ORDER);
                            $page_links = $matches[2];

                            /**
                             * get table data on page.
                             */
                            $matches3 = [];
                            $etaz = '';
                            preg_match_all('/<table class="item" .*>\s*<.*>\s*<th>([a-zA-Zа-яёА-ЯёЁ 0-9ътыруь щ чЪТЫРУЬ Щ Ч]*)<.*>\s*<.*>\s*<.*>\s*(?:<.*>)*\s*([a-zA-Zа-яёА-ЯёЁ 0-9\S]*)/im',
                                $response[0], $matches3, PREG_PATTERN_ORDER);
                            if (isset($matches3[1]) && !empty($matches3[1])) {
                                foreach ($this->search_fields as $key => $field) {
                                    for ($i = 0; $i < count($matches3[1]); $i++) {
                                        if (strstr($matches3[1][$i], $key)) {
                                            $this->search_fields[$key] = $matches3[2][$i];
                                        }
                                        if ($key === 'Этаж' && preg_match('((Этаж)\s*?$)', $matches3[1][$i]) === 1) {
                                            $etaz = $matches3[2][$i];
                                        }
                                    }
                                }
                                $this->search_fields['Этаж'] = $etaz;
                            }
                            unset($matches3);
                            /**
                             * get body of ad.
                             */
                            $matches4 = [];
                            preg_match_all('/<div .*\s* id="textContent">\s*.*<p .*>((\s*.*(<.*\/.?>))*)/im',
                                //   preg_match_all('/<div.*id="textContent">\s*<?.*>?\s*([a-zA-Zа-яёА-ЯёЁ 0-9ътыруь щ чЪТЫРУЬ Щ Чі.№їІ:,&;\-\/єЄ]|(<br\s*\/>)|\s)*/im',
                                $response[0], $matches4, PREG_PATTERN_ORDER);
                            $matches4[1] = preg_replace("/((<.*\s*>)|(&quot))/", "", $matches4[1]);
                            $matches4[1] = preg_replace("/\s{2,}/", " ", $matches4[1]);
                            if (isset($matches4[1]))
                                $this->search_fields['message'] = implode('', $matches4[1]);
                            unset($address);

                            $address = [];
                            preg_match_all('/<div\s*.*\s* class="offer-titlebox__details">\s*(<.*>)?(<strong.*?>([a-zA-Zа-яёА-ЯёЁ:;.,\-?! 0-9\S]*)<\/strong>)/im',
                                $response[0], $address, PREG_PATTERN_ORDER);
                            if (isset($address[3]) && !empty($address[3])) {
                                $this->search_fields['address'] = implode('', $address[3]);
                            }
                            unset($address);


                            preg_match_all('/<div\s*.*\s* class="offer-titlebox__details">\s*.*\s*(<em.*>\s*([a-zA-Zа-яёА-ЯёЁ:;.,\-?! 0-9\S]*)\s*<\/em>)/im',
                                $response[0], $address, PREG_PATTERN_ORDER);
                            if (isset($address[2]) && !empty($address[2])) {
                                $address[2] = implode($address[2]);
                                $address[2] = preg_replace("/((<.*\s*>)|(&quot))/", "", $address[2]);
                                $address[2] = preg_replace("/\s{2,}/", " ", $address[2]);
                                $this->search_fields['ad_added'] = $address[2];
                            }
                            unset($address);


                            preg_match_all('/<div\s*.* class="price-label">\s*(<strong.*?>([a-zA-Zа-яёА-ЯёЁ:;.,\-?! 0-9\S]*)<\/strong>)/im',
                                $response[0], $price, PREG_PATTERN_ORDER);
                            if (isset($price[2]) && !empty($price[2])) {
                                $this->search_fields['price'] = implode('', $price[2]);
                            }
                            unset($price);


                            preg_match_all('/<div\s*.*\s* class="tcenter img-item">\s*.*\s*(.*src="(https?.*(.jpg)|(.jpeg)|(.png)|(.gif)|(.svg)"))/im',
                                $response[0], $photo_links, PREG_PATTERN_ORDER);
                            // <div\s*.*\s* class="tcenter img-item">\s*.*\s*(.*src="(https?.*(.jpg)|(.jpeg)|(.png)|(.gif)|(.svg)"))
                            if (isset($photo_links[2]) && !empty($photo_links[2])) {
                                $this->search_fields['photos_src'] = $photo_links[2];
                            }
                            unset($photo_links);


                            preg_match_all('/<div\s*.*\s* class="offer-titlebox">\s*.*\s*([^\<]*)/im',
                                $response[0], $title, PREG_PATTERN_ORDER);
                            // <div\s*.*\s* class="tcenter img-item">\s*.*\s*(.*src="(https?.*(.jpg)|(.jpeg)|(.png)|(.gif)|(.svg)"))
                            if (isset($title[1]) && !empty($title[1])) {
                                $this->search_fields['title'] = implode('', $title[1]);
                            }
                            unset($title);

                            //$this->proxy_two($link);
                            /**
                             * get correct telephone.
                             */
                            $phoneId = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', substr($idPhone[0], 5));
                            $telResp = $this->proxy_ip_test("https://www.olx.ua/ajax/misc/contact/phone/" . $phoneId . "/", $PHPSESSID, $pt);
                            $talephone = $this->getTelephoneBody(implode('', $telResp[2]));

                            /*-----------------------------------------------------------------------------------------------------*/
                            //$el=collect([]);
                            /*             $ad = new_ad::where('link', '=', $link)->first();
                                         if(!empty($ad)){
                                             if($ad->title_ad !== $this->search_fields['title'])
                                                 $ad->title_ad=$this->search_fields['title'];
                                             if($ad->body_ad !== $this->search_fields['message'])
                                                 $ad->body_ad = $this->search_fields['message'];
                                             if($ad->price !== $this->search_fields['price'])
                                                 $ad->price = $this->search_fields['price'];
                                             if($ad->ad_from !== $this->search_fields['Объявление от'])
                                                 $ad->ad_from = $this->search_fields['Объявление от'];
                                             if($ad->category !== $this->search_fields['Выберите рубрику'])
                                                 $ad->category = $this->search_fields['Выберите рубрику'];
                                             if($ad->coop_with_realtors !== $this->search_fields['Готов сотрудничать с риэлторами'])
                                                 $ad->coop_with_realtors = $this->search_fields['Готов сотрудничать с риэлторами'];
                                             if($ad->object_type !== $this->search_fields['Тип объекта'])
                                                 $ad->object_type = $this->search_fields['Тип объекта'];
                                             if($ad->floor !== intval($this->search_fields['Этаж']))
                                                 $ad->floor = intval($this->search_fields['Этаж']);
                                             if($ad->num_of_storeys !== intval($this->search_fields['Этажность']))
                                                 $ad->num_of_storeys = intval($this->search_fields['Этажность']);
                                             if($ad->total_area !== floatval($this->search_fields['Общая площадь']))
                                                 $ad->total_area = floatval($this->search_fields['Общая площадь']);
                                             if($ad->kitchen_area !== floatval($this->search_fields['Площадь кухни']))
                                                 $ad->kitchen_area = floatval($this->search_fields['Площадь кухни']);
                                             if($ad->number_of_rooms !== intval($this->search_fields['Количество комнат']))
                                                 $ad->number_of_rooms = intval($this->search_fields['Количество комнат']);
                                             if($ad->layout !== $this->search_fields['Планировка'])
                                                 $ad->layout = $this->search_fields['Планировка'];
                                             if($ad->bathroom !== $this->search_fields['Санузел'])
                                                 $ad->bathroom = $this->search_fields['Санузел'];
                                             if($ad->heating !== $this->search_fields['Отопление'])
                                                 $ad->heating = $this->search_fields['Отопление'];
                                             if($ad->repairs !== $this->search_fields['Ремонт'])
                                                 $ad->repairs = $this->search_fields['Ремонт'];
                                             if($ad->appliances !== $this->search_fields['Бытовая техника'])
                                                 $ad->appliances = $this->search_fields['Бытовая техника'];
                                             if($ad->comfort !== $this->search_fields['Комфорт'])
                                                 $ad->comfort = $this->search_fields['Комфорт'];
                                             if($ad->communications !== $this->search_fields['Коммуникации'])
                                                 $ad->communications = $this->search_fields['Коммуникации'];
                                             if($ad->infrastructure !== $this->search_fields['Инфраструктура (до 500 метров)'])
                                                 $ad->infrastructure = $this->search_fields['Инфраструктура (до 500 метров)'];
                                             if($ad->landscape !== $this->search_fields['Ландшафт (до 1 км.)'])
                                                 $ad->landscape = $this->search_fields['Ландшафт (до 1 км.)'];
                                             if($ad->telephones !== $talephone)
                                                 $ad->telephones = $talephone;
                                             if($ad->ad_added !== $this->search_fields['ad_added'])
                                                 $ad->ad_added = $this->search_fields['ad_added'];
                                             if($ad->house_type !== $this->search_fields['Тип дома'])
                                                 $ad->house_type = $this->search_fields['Тип дома'];
                                             if($ad->commission !== $this->search_fields['Без комиссии'])
                                                 $ad->commission = $this->search_fields['Без комиссии'];
                                             if($ad->dist_to_the_near_city !== $this->search_fields['Расстояние до ближайшего города'])
                                                 $ad->dist_to_the_near_city = $this->search_fields['Расстояние до ближайшего города'];
                                             if($ad->property_type !== $this->search_fields['Тип недвижимости'])
                                                 $ad->property_type = $this->search_fields['Тип недвижимости'];
                                             if($ad->land_area !== $this->search_fields['Площадь участка'])
                                                 $ad->land_area = $this->search_fields['Площадь участка'];
                                             if($ad->buildings_on_plot !== $this->search_fields['Постройки на участке'])
                                                 $ad->buildings_on_plot = $this->search_fields['Постройки на участке'];
                                             if($ad->furnishing !== $this->search_fields['Меблирование'])
                                                 $ad->furnishing = $this->search_fields['Меблирование'];
                                             if($ad->multimedia !== $this->search_fields['Мультимедиа'])
                                                 $ad->multimedia = $this->search_fields['Мультимедиа'];
                                             $ad->save();
                                         }else {*/
                            /*-----------------------------------------------------------------------------------------------------*/

                            $ad = new_ad::create(['link' => $link,
                                'title_ad' => $this->search_fields['title'],
                                'body_ad' => $this->search_fields['message'],
                                'price' => $this->search_fields['price'],
                                'ad_from' => $this->search_fields['Объявление от'],
                                'category' => $this->search_fields['Выберите рубрику'],
                                'coop_with_realtors' => $this->search_fields['Готов сотрудничать с риэлторами'],
                                'object_type' => $this->search_fields['Тип объекта'],
                                'floor' => intval($this->search_fields['Этаж']),
                                'num_of_storeys' => intval($this->search_fields['Этажность']),
                                'total_area' => floatval($this->search_fields['Общая площадь']),
                                'kitchen_area' => floatval($this->search_fields['Площадь кухни']),
                                'number_of_rooms' => intval($this->search_fields['Количество комнат']),
                                'layout' => $this->search_fields['Планировка'],
                                'bathroom' => $this->search_fields['Санузел'],
                                'heating' => $this->search_fields['Отопление'],
                                'repairs' => $this->search_fields['Ремонт'],
                                'appliances' => $this->search_fields['Бытовая техника'],
                                'comfort' => $this->search_fields['Комфорт'],
                                'communications' => $this->search_fields['Коммуникации'],
                                'infrastructure' => $this->search_fields['Инфраструктура (до 500 метров)'],
                                'landscape' => $this->search_fields['Ландшафт (до 1 км.)'],
                                'photo_counter' => 0,
                                'telephones' => $talephone,
                                'ad_added' => $this->search_fields['ad_added'],
                                'house_type' => $this->search_fields['Тип дома'],
                                'commission' => $this->search_fields['Без комиссии'],
                                'dist_to_the_near_city' => $this->search_fields['Расстояние до ближайшего города'],
                                'property_type' => $this->search_fields['Тип недвижимости'],
                                'land_area' => $this->search_fields['Площадь участка'],
                                'buildings_on_plot' => $this->search_fields['Постройки на участке'],
                                'furnishing' => $this->search_fields['Меблирование'],
                                'multimedia' => $this->search_fields['Мультимедиа']
                            ]);
                            //  }

                            //$this->saveImagesFromBody($this->search_fields['photos_src'], $ad);
                            $this->save_page_images($this->search_fields['photos_src'], $ad);

                            $count = new_ad::all()->count();
                            Pusher::trigger('my-channel', 'my-event', ['message' => '' . $count]);

                        }
                    }
                }
                return $this->search_fields;
            }else return 'Links list was empty.';
        }catch (\Exception $exception){
            return [$exception->getMessage(), $exception->getTrace()];
            //<table class="item" .*>\s*<.*>\s*<th>([a-zA-Zа-яА-ЯёЁ 0-9]*)<.*>\s*<.*>\s*<.*>\s*(<.*>)*\s*([a-zA-Zа-яА-ЯёЁ 0-9\S]*)
        }
    }

    /**
     * Save image for parse page.
     * @param $imagesList
     * @param new_ad $ad
     * @return array|bool
     */
    public function saveImagesFromBody($imagesList, new_ad $ad)
    {
        try {
            if(!empty($imagesList)) {

                // Get path for images store.
                $filePath = storage_path('app/public/testing');
                $dir = new Filesystem;
                if(!$dir->exists($filePath)){
                    $dir->makeDirectory($filePath);  //follow the declaration to see the complete signature
                }
                $counter = 0;
                foreach ($imagesList as $image) {
                    $extention = pathinfo($image, PATHINFO_EXTENSION);
                    if(($extention == 'jpg') || ($extention == 'jpeg') ||
                        ($extention == 'png') || ($extention == 'gif') ||
                        ($extention == 'svg')){
                        $name = md5(uniqid(rand(), true));
                        $proxy = $this->proxy_for_image->random();
                        $response = Curl::to($image)
                          //  ->withProxy($this->last_proxy[0], $this->last_proxy[1])
                          //  ->withProxy($proxy->ip, $proxy->port)
                            ->withContentType('image/'.$extention)
                            ->download($filePath.'/'.$name.'.'.$extention);
                        $counter++;
                        new_ad_image::create(['img_name' => $name.'.'.$extention,
                            'img_path' => 'storage/testing/'.$name.'.'.$extention,
                            'ad_id' => $ad->id]);
                    }
                }
                $ad->photo_counter = $counter;
                $ad->save();
                return true;
            }
            return false;
        }catch (\Exception $e){
            return $e->getTrace();
        }
    }

    function __destruct() {
        $this->keys = null;
    }

    /**
     * @param $text
     * @return array
     */
    private function getCookies($text) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $text, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        return $cookies;
    }

    /**
     * @param $url
     * @param string $proxy
     * @return array
     */
    protected function getContentCURL($url, $proxy='47.206.51.67:8080', $cookie=null, $pt=null){
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);
       // $proxy='89.236.17.106:3128';
       //$proxy='47.206.51.67:8080';
       // if(empty($proxy))
       // $proxy='217.61.121.110:3128';

        $ch = curl_init();
        if(!empty($pt)){
            $url.='/?pt='.$pt;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, array_rand($this->userAgents, 1));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if(!empty($cookie)){
            $strCookie = "PHPSESSID=".$cookie;
            curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //required for https urls

        $response = curl_exec($ch);

        $status = curl_getinfo($ch);
        // Разделяем строку полученного ответа по маркерам окончания строки
        $result_answer = explode("\r\n", $response);
        curl_close($ch);
        return [$response, $status, $result_answer];
    }

    /**
     * Method for get telephone from object.
     * @param {array} $array
     * @return string
     */
    protected function getTelephoneBody(string $array):string {
        if(!empty($array)){
           // $str = $array;
            $matches = []; $result = '';
            preg_match('/{"value":"[0-9 ]+"}/', $array, $matches);
            if(count($matches)>0){
                $result = preg_replace('/{"value":"([0-9 ]+)"}/','$1',$matches[0]);
                $result = preg_replace("/(\s)+/", "$1", $result);
                $result = preg_replace("/( )+/", "", $result);
                return $result;
            }
        }
        return '';
    }

    /**
     * Method for parse US ip proxy.
     * @param void
     * @return array
     */
    public function parseProxy():array {
        try{
            $link = 'http://proxy-ip-list.com/free-usa-proxy-ip.html';
            $response = Curl::to($link)
                ->withHeader('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8')
                ->withHeader('Accept-Language: ru,en-us;q=0.7,en;q=0.3')
                ->withHeader('Accept-Encoding: deflate')
                ->withHeader('Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7')
                ->withData('action=login&imembername=valenok&ipassword=ne_skaju&submit=%C2%F5%EE%E4')
                ->get();
         //   $response = $this->getContentCURL($link);
            // Create new instance for parser.
            $crawler = new Crawler(null, $link);
            $crawler->addHtmlContent($response, 'UTF-8');
            $matches = [];
            if(!empty($crawler)) {
                $mainDiv = $crawler->filterXPath('//tbody[contains(@class, "table_body")]')->html();
                $crawlerSection = new Crawler();
                $crawlerSection->addHtmlContent($mainDiv);
                $mainContent = $crawler->filter('td')->each(function (Crawler $node, $i) {
                    return $node->text();
                });
                $str = implode(' ',$mainContent);
                preg_match_all('/([0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}:([0-9]{4}))/mi',
                    $str, $matches, PREG_PATTERN_ORDER);

                    if(!empty($matches[0])&&!empty($matches[2])) {
                        for($i=0;$i<count($matches[0]);$i++){
                            try {
                             //   if ($matches[0][$i] !== proxy_list::latest()->get()->ip)
                                    proxy_list::create(['ip' => $matches[0][$i],
                                        'port' => $matches[2][$i], 'counter' => 0]);
                            } catch (\Exception $e) {};
                        }
                    }
                    //
            }
            return $matches;
        }catch (\Exception $exception){
            return [$exception->getMessage(), $exception->getTrace()];
        }
    }

    /**
     * Parser proxy ip method.
     * @param string $testlink
     * @return array|string
     */
    public function proxy_two($testlink='https://www.olx.ua/nedvizhimost/kha/'){
        try{

            Pusher::trigger('proxy-channel-start', 'my-event-proxy-start', ['indicate' => false]);

            $link = 'https://free-proxy-list.net/';
            $path = storage_path('app/public/timeImg');
            $response = Curl::to($link)
                ->withHeader('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8')
                ->withHeader('Accept-Language: ru,en-us;q=0.7,en;q=0.3')
                ->withHeader('Accept-Encoding: deflate')
                ->withHeader('Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7')
                ->withData('action=login&imembername=valenok&ipassword=ne_skaju&submit=%C2%F5%EE%E4')
                ->setCookieJar( $path.'/Cookie.txt')
                ->get();
       //      $response = $this->getContentCURL($link);
             if(!empty($response)) {
                 $crawler = new Crawler(null, $link);
                 $crawler->addHtmlContent($response, 'UTF-8');
                 //   $matches = [];
                 $all_arr = [];
                 if (!empty($crawler)) {
                     $mainDiv = $crawler->filterXPath('//section[contains(@id, "list")]')->html();
                     $crawlerSection = new Crawler();
                     $crawlerSection->addHtmlContent($mainDiv);
                     $DivCont = $crawler->filterXPath('//div[contains(@class, "table-responsive")]')->html();

                     $crawlerTab = new Crawler();
                     $crawlerTab->addHtmlContent($DivCont);
                     $table = $crawler->filterXPath('//table[contains(@class, "table table-striped table-bordered")]')->html();

                     preg_match_all('/(<td>([0-9]{1,5})<\/td>)/mi',
                         $table, $matches, PREG_PATTERN_ORDER);
                     $port = $matches[2];

                     preg_match_all('/(<td>([0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3})<\/td>)/mi',
                         $table, $matches1, PREG_PATTERN_ORDER);
                     $ip = $matches1[2];

                     preg_match_all('/(<td>((elite proxy|transparent|anonymous))<\/td>)/mi',
                         $table, $matches2, PREG_PATTERN_ORDER);
                     $type = $matches2[2];

                     preg_match_all('/(<td class="hx">([A-Za-z]+)<\/td>)/mi',
                         $table, $matches3, PREG_PATTERN_ORDER);
                     $https = $matches3[2];

                     $list = collect([]); $forDel=collect([]);
                     try {
                         $list = proxy_test_list::where('id', '>', 0)->take(300)->orderBy('created_at', 'asc')->get();
                     }catch(\Exception $e){};

                     // $testlink = 'https://www.olx.ua/nedvizhimost/kha/';
                     for ($i = 0; $i < count($port); $i++) {
                         try {
                             #open Test
                             proxy_test_list::create(['ip' => $ip[$i], 'proxy_type' => $type[$i],
                                 'bool_https' => $https[$i] === 'yes' ? 'https' : 'http', 'port' => $port[$i]]);


                             if($list->isNotEmpty()){
                                 $min = $list->min('rating');
                                 $list->where('rating','=',$min)->first()->delete();
                             }

                             Pusher::trigger('proxy-channel', 'my-event-proxy', ['ip' => '' . $ip[$i], 'proxy_type' => '' . $type[$i],
                                 'bool_https' => $https[$i] === 'yes' ? 'https' : 'http', 'port' => '' . $port[$i], 'res' => true]);

                         } catch (\Exception $e) {
                             Pusher::trigger('proxy-channel', 'my-event-proxy', ['ip' => '' . $ip[$i], 'proxy_type' => '' . $type[$i],
                                 'bool_https' => $https[$i] === 'yes' ? 'https' : 'http', 'port' => '' . $port[$i], 'res' => false]);
                         }
                     }
                     Pusher::trigger('proxy-channel-start', 'my-event-proxy-start', ['indicate' => true]);
                     return [$port, $ip, $type, $https, proxy_test_list::all()->count()];
                 } else {
                     Pusher::trigger('proxy-channel-start', 'my-event-proxy-start', ['indicate' => true]);
                     return 'error';
                 }
             }
            Pusher::trigger('proxy-channel-start', 'my-event-proxy-start', ['indicate' => true]);
            return 'error';
            //   return $response;
        }catch (\Exception $exception){
            return [$exception->getMessage(), $exception->getTrace()];
        }
    }

    /**
     * Test of proxy ip/port method.
     * @param $link
     * @param null $cookie
     * @param null $pd
     * @return array|null
     */
    protected function proxy_ip_test($link, $cookie=null, $pd=null){
        try {
          //  $this->proxy_list->where('id', '>', $this->position);
            foreach ($this->proxy_list as $proxy_element) {
                $proxy = $proxy_element->ip . ':' . $proxy_element->port;
                if(empty($cookie) && empty($pd))
                    $response = $this->getContentCURL($link, $proxy);
                else
                    $response = $this->getContentCURL($link, $proxy, $cookie, $pd);
                if ($response[0] !== FALSE || $response[1]['http_code'] == 200) {
                    $proxy_element->rating++;
                    $proxy_element->save();
                    $this->last_proxy = [$proxy_element->ip, $proxy_element->port];
                    return $response;
                }
                else{
                    $proxy_element->rating--;
                    $proxy_element->save();
                }
            }
            return null;
        }catch (\Exception $exception){
            return null;
        }
    }

    /**
     * Get all links from main page.
     * @param $region
     * @param string $page_size
     * @return array
     */
    public function GetAllLinks($region='pol/', $page_size=null):array {
        try {
        //    $this->proxy_two('');
            $links = []; $pages = []; $globalLinksArr = [];
           // array_push($links, 'https://www.olx.ua/nedvizhimost/' . $region);
            $response = $this->proxy_ip_test('https://www.olx.ua/nedvizhimost/' . $region);
            if(!empty($response) && !empty($response[0])&&!empty($response[2])) {

                preg_match_all('/(href="(https?.*.html)#)/im',
                    $response[0], $matches, PREG_PATTERN_ORDER);
                if(isset($matches[2])&&!empty($matches[2])) {
                    /*$links*/$globalLinksArr = array_merge($links, $matches[2]);
                }
                unset($matches);

                preg_match_all('/(href="(https?(www)?.*.?page=([0-9]+))")/im',
                    $response[0], $matches, PREG_PATTERN_ORDER);
                if(isset($matches[4])&&!empty($matches[4]) ) {
                    $pages = array_unique(array_merge($pages, $matches[4]));
                }
                unset($matches);

                if(count($pages)>0)
                    $min_max_pages = [intval(min($pages)), intval(max($pages))];
                else
                    $min_max_pages = [0, 0];

                if(!empty($page_size)) {
                    for ($j = $min_max_pages[0]; $j < $page_size + 1; $j++) {
                        array_push($links, 'https://www.olx.ua/nedvizhimost/' . $region . '?page=' . $j);
                    }
                    foreach ($links as $link){
                            $response = $this->proxy_ip_test($link);
                            if (!empty($response) && !empty($response[0]) && !empty($response[2])) {

                                preg_match_all('/(href="(https?.*.html)#)/im',
                                    $response[0], $matches, PREG_PATTERN_ORDER);
                                if (isset($matches[2]) && !empty($matches[2])) {
                                    $globalLinksArr = array_merge($globalLinksArr, $matches[2]);
                                }
                               // $globalLinksArr[]=$matches;
                                unset($matches);
                            }
                    }
                }else{
                    for ($j = $min_max_pages[0]; $j < $min_max_pages[1] + 1; $j++) {
                        array_push($links, 'https://www.olx.ua/nedvizhimost/' . $region . '?page=' . $j);
                    }
                    foreach ($links as $link){
                            $response = $this->proxy_ip_test($link);
                            if (!empty($response) && !empty($response[0]) && !empty($response[2])) {

                                preg_match_all('/(href="(https?.*.html)#)/im',
                                    $response[0], $matches, PREG_PATTERN_ORDER);
                                if (isset($matches[2]) && !empty($matches[2])) {
                                    $globalLinksArr = array_merge($globalLinksArr, $matches[2]);
                                }
                                unset($matches);
                            }
                    }
                }
                return $globalLinksArr;
            }
            $globalLinksArr = [];

            return [$globalLinksArr, $response];
        }catch (\Exception $exception){
            return [$exception->getMessage(), $exception->getTrace()];
        }
    }


    public function save_page_images($imagesList, new_ad $ad){
        try {
                if (!empty($imagesList)) {

                    // Get path for images store.
                    $filePath = storage_path('app/public/testing');
                    $dir = new Filesystem;
                    if (!$dir->exists($filePath)) {
                        $dir->makeDirectory($filePath);  //follow the declaration to see the complete signature
                    }
                    $counter = 0;
                    foreach ($imagesList as $image) {
                        $extention = pathinfo($image, PATHINFO_EXTENSION);
                        if (($extention == 'jpg') || ($extention == 'jpeg') ||
                            ($extention == 'png') || ($extention == 'gif') ||
                            ($extention == 'svg')) {
                            $name = md5(uniqid(rand(), true));
                            $this->save_image($image, $filePath.'/'.$name.'.'.$extention);
                            $counter++;
                            new_ad_image::create(['img_name' => $name.'.'.$extention,
                                'img_path' => 'storage/testing/'.$name.'.'.$extention,
                                'ad_id' => $ad->id]);
                        }
                    }
                    $ad->photo_counter = $counter;
                    $ad->save();
                    return true;
                }
                else return false;
            }catch (\Exception $exception) {
                return $exception->getTrace();
            }
        }

    public function save_image($image_url, $image_file){

        $fp = fopen ($image_file, 'w+'); // open file handle

        $ch = curl_init($image_url);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
        curl_setopt($ch, CURLOPT_FILE, $fp); // output to file
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        # proxy block.
     //   $proxy = $this->proxy_list->random();
     //   curl_setopt($ch, CURLOPT_PROXY, ''.$proxy->ip.':'.$proxy->port);
      //  curl_setopt($ch, CURLOPT_PROXYUSERPWD, "USER:PASS");
     //   curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 1000); // some large value to allow curl to run for a long time
        curl_setopt($ch, CURLOPT_USERAGENT, array_rand($this->userAgents, 1));
        // curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable this line to see debug prints
        curl_exec($ch);

        curl_close($ch); // closing curl handle
        fclose($fp); // closing file handle
    }

}