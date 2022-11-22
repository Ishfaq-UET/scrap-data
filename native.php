<?php
require 'vendor/autoload.php';
use Goutte\Client;


function scrapePage($url, $client, $file)
{
    print_r('scraping page: '. $url. PHP_EOL);
    $data = [];
    $crawler = $client->request('GET', $url);
    $count = 0;
    $crawler->filter('._7e3920c1')->each(function ($node, $index) use($client, $data, $file) {
        $link ="https://www.olx.com.pk" . $node->filter('.ee2b0479 a')->attr('href');
        $data = scrapeAd($link, $client);
        // foreach ($data as $line) {
            fputcsv($file, $data);
        //   }
    });
    try {
        $next_page = $crawler->filter('._95dae89d')->attr('href');
    } catch (InvalidArgumentException) {
        return null;
    }
    return "https://www.olx.com.pk" . $next_page;
}


// function scrapeAd($url, $client, $file) {
//     print_r($url);
//     $crawler = $client->request('GET', $url);

//     $userName = $crawler->filter('._6caa7349 > span')->text();
//     $phoneNumber = $crawler->filter('.b34f9439 > span')->text();

//     print_r($userName); 
//     print_r($phoneNumber);
//     fwrite($file, $userName);
    
//     // die();
// }





// main code starts here.

$client = new Client();
$nextUrl = "https://www.olx.com.pk/items?page=1";
$crawler = $client->request('GET', 'https://www.olx.com.pk/items');
$file = fopen("olx.csv","a");
$count = 1;
while ($nextUrl) {
    print_r('--------Page No : ' . $count . '---------');
    $count++;
    $nextUrl = scrapePage($nextUrl, $client, $file);
}
fclose($file);


function scrapeAd($url) {
    print_r('scraping ad: '. $url. PHP_EOL);
    // $url = 'https://www.olx.com.pk/item/ducati-gt-edition-in-400cc-replica-2023-model-iid-1058538476';
    $result = parse_url( $url);
    $last = explode('-', strstr($url, 'iid'));
    $id = $last[1];
    
    $html = file_get_contents($url);
    $needle = '<script>';
    $needle2 = '</script>';
    $lastPos = 0;
    $positions = array();

    while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
        $positions[] = $lastPos;
        $lastPos = $lastPos + strlen($needle);
    }
    $firstScript = $positions[2];
    $lastScript = strpos($html, $needle2, $firstScript);
    $string  = substr($html, $firstScript, ($lastScript +  9)-$firstScript);

    $needle3 = '{"adExternalID":"'. $id .'","format":"lite"},';
    $needle4 = ',"error"';
    $dataPosStart = strpos($string, $needle3);
    $dataPosStart += strlen($needle3);

    $string  = substr($string, $dataPosStart);

    $dataPosEnd = strpos($string, $needle4); 
    $string  = '{'.substr($string, 0, $dataPosEnd).'}';
    $string = json_decode($string);
    // print_r($string);
    // die();
    $data['userName'] = (string)$string->data->name ?? 'null';
    $data['phoneNumber'] = strval($string->data->phoneNumber) ?? 'null';
    $data['phoneNumber'] = str_replace("+","",$data['phoneNumber']);
    // print_r(json_encode($data));
    return $data;
}
