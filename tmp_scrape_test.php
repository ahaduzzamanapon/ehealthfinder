<?php
require 'vendor/autoload.php';

$html = file_get_contents('tmp_shajgoj.html');
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$articles = $xpath->query('//article');
echo "Found " . $articles->length . " articles.\n";

foreach ($articles as $index => $article) {
    if ($index > 2) break;
    
    // Get title & link
    $titleNode = $xpath->query('.//h3[contains(@class, "entry-title")]/a', $article);
    if ($titleNode->length > 0) {
        $title = $titleNode->item(0)->textContent;
        $link = $titleNode->item(0)->getAttribute('href');
        echo "Title: $title\n";
        echo "Link: $link\n";
    }

    // Get image
    $imgNode = $xpath->query('.//img', $article);
    if ($imgNode->length > 0) {
        $imgUrl = $imgNode->item(0)->getAttribute('src');
        if (strpos($imgUrl, 'data:image') !== false) {
             $imgUrl = $imgNode->item(0)->getAttribute('data-src') ?: $imgUrl;
        }
        echo "Image: $imgUrl\n";
    }
    echo "====================================\n";
}
