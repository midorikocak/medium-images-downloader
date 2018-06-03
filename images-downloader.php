<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

function get_filename($url)
{
    $parts = parse_url($url);

    return basename($parts['path']);
}

function get_image($url)
{
    $url = preg_replace('/\/max\/(\d+)/', '', $url);
    echo "\t"."Downloading ".$url."\n";
    if (! file_exists('./export/img')) {
        mkdir('./export/img', 0777, true);
    }

    $newName = get_filename($url);
    //$url = str_replace('max/800', 'max/1600', $url);
    $newTarget = './export/img/'.$newName;
    if (! file_exists($newTarget)) {
        file_put_contents($newTarget, file_get_contents($url));
    }
}

function get_images($filename)
{
    $file = file_get_contents($filename);

    $crawler = new Crawler($file);
    $images = $crawler->filter('img')->extract(['src']);
    echo "Downloading ".$filename."\n";
    foreach ($images as $image) {
        get_image($image);
    }
    remove_url($filename);
}

function remove_url($file)
{
    if (! file_exists('./export')) {
        mkdir('./export', 0777, true);
    }
    $fileContents = file_get_contents($file);
    preg_match_all('@src="([^"]+)"@', $fileContents, $imagesContainer);
    $images = $imagesContainer[0];
    foreach ($images as $image) {
        $newName = 'src="./img/'.get_filename($image);
        $fileContents = str_replace($image, $newName, $fileContents);
    }
    file_put_contents('./export/'.$file, $fileContents);
}

function filename_array_filter($filename)
{
    $exists = $filename;
    $startsWithDate = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", '1984-10-05midori.php');
    $extensionIsHtml = pathinfo($filename, PATHINFO_EXTENSION) == 'html';
    $hasHashAtTheEnd = ctype_xdigit(substr(pathinfo($filename, PATHINFO_FILENAME), -12));
    return $exists && $startsWithDate && $extensionIsHtml && $hasHashAtTheEnd;
}

function download_images(){
    $files = array_diff(scandir('.'), ['..', '.']);
    $mediumFiles = array_filter($files, 'filename_array_filter');

    foreach ($mediumFiles as $file){
        remove_url($file);
    }
}

download_images();

?>