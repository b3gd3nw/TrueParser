<?php

namespace App\Parsers;

use App\Controllers\ProxyController;
use App\Interfaces\IParser;
use App\Core\Connect;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class PageParser extends Connect implements IParser
{
    public $new_url;
    public $class;

    /**
     * Connects to the link and gets content
     *
     * @param  string  $url
     * @return \DOMXPath
     */
    public function getPage($url)
    {
        $links_log = new Logger('ERROR LINKS');
        $links_log->pushHandler(new StreamHandler(__DIR__ . '/../log/error_links.log', Logger::INFO));

        $proxy_list = new ProxyController();
        $proxy_list->init();

        do {
            $proxy = trim($this->connect->get('proxy', '\n'));
            $valid = $proxy_list->proxy_cheker($proxy);
            var_dump($valid);
        }
        while(!$valid);

        $this->connect->set('proxy', $proxy);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => $proxy,
            CURLOPT_CONNECTTIMEOUT => 5,
        ];

        $ch = curl_init( $url);
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close( $ch );

        // Check on errors
        if ($httpCode == 404){
            $links_log->info('Parsed String: ', array( 'error' => 'Error 404', 'link' => $url));
            exit('Error 404. Task destroyed' . PHP_EOL);
        } else if ($httpCode == 403){
            $links_log->info('Parsed String: ', array( 'error' => 'Error 403', 'link' => $url));
            $this->connect->set('tasks', json_encode((array)['class' => 'PageParser', 'url' => $url]));
            exit('Error 403. Task returned to queue' . PHP_EOL);
        }

        // Check on bad connect
        if ($content == false){
            $this->connect->set('tasks', json_encode((array)['class' => 'PageParser', 'url' => $url]));
            exit('Bad connected. Task returned to queue' . PHP_EOL);
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($content);
        return new \DOMXPath($dom);



    }

    /**
     * Parse content and writes links to the queue
     *
     * @param  string  $url
     */
    public function parse($url)
    {
        $links_log = new Logger('PARSED LINKS');

        $links_log->pushHandler(new StreamHandler(__DIR__ . '/../log/links.log', Logger::INFO));

        $xpath = $this->getPage($url);

        $nav = $xpath->query("//ul[contains(@class, 'dnrg')]/*");

        foreach ($nav as $item)
        {
            $baseURI = $item->baseURI;

            foreach ($item->getElementsByTagName('a') as $link)
            {
                $href = $link->getAttribute('href');
                $new_url = $baseURI . $href;

                if(strpos($href, '-')){
                    $this->class = 'QuestionParser';
                } else {
                    $this->class = 'PageParser';
                }
                $data = json_encode((array)['class' => "{$this->class}",
                    'url' => $new_url]);
                $this->connect->set('tasks' ,$data);
                $links_log->info('prsed link: ', array( 'link' => $data ));
            }
        }
    }
}