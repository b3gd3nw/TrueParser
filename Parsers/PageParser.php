<?php

namespace App\Parsers;

use App\Interfaces\IParser;
use App\Core\Connect;

class PageParser extends Connect implements IParser
{
    private $url;
    public $new_url;

    public function getPage($url)
    {
        $content = file_get_contents($url);
        $dom = new \DOMDocument();
        @$dom->loadHTML($content);
        return new \DOMXPath($dom);
    }

    public function parse($url)
    {
        var_dump(228);
        $xpath = $this->getPage($url);
        $nav = $xpath->query("//ul[contains(@class, 'dnrg')]/*");
//        var_dump($nav);
        foreach ($nav as $item)
        {
            $baseURI = $item->baseURI;

            foreach ($item->getElementsByTagName('a') as $link)
            {
 //               var_dump($link);
                $href = $link->getAttribute('href');
                $new_url = $baseURI . $href;
                $this->connect->set();
                var_dump($new_url);

            }
        }
    }
}