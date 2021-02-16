<?php

namespace App\Parsers;

use App\Controllers\ProxyController;
use App\Interfaces\IParser;
use App\Core\Connect;

class PageParser extends Connect implements IParser
{
    private $url;
    public $new_url;

    public function getPage($url)
    {
        $proxy_list = new ProxyController();
        $proxy_list->init();

        do {
            $proxy = $this->connect->get('proxy');
            $valid = $proxy_list->proxy_cheker($proxy);
            var_dump(99);
        }
        while(!$valid);

            $this->connect->set('proxy', $proxy);

            $context = array('http' => array('proxy' => "{$proxy}",'request_fulluri' => true,),);
            $stream = stream_context_create($context);
            $content = file_get_contents($url, false, $stream);
            $realIP = file_get_contents("http://ipecho.net/plain", false, $stream);
            var_dump($realIP);
 //           var_dump($proxy);
//        $ip = $content->find('span#ip', 0)->innertext;
//        var_dump($ip);
            $dom = new \DOMDocument();
            @$dom->loadHTML($content);
            return new \DOMXPath($dom);



    }

    public function parse($url)
    {
        var_dump(228);
        if(!$this->error_check($url))
        {
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

                    $data = json_encode((array)['class' => 'PageParser',
                        'url' => $new_url]);
                    $this->connect->set('tasks' ,$data);
    //                var_dump($data);
    //                $this->connect->get('tasks');
                }
            }
        } else {
            exit('Error 404');
        }

    }

    public function error_check($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
            return true;
        }

        curl_close($handle);
        return false;
        /* Handle $response here. */
    }
}