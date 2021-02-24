<?php

namespace App\Parsers;

use App\Controllers\ProxyController;
use App\Interfaces\IParser;
use App\Core\Connect;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class QuestionParser extends Connect implements IParser
{
    public $new_url;

    public array $questions_arr = [];
    public array $answers_arr = [];

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
            $proxy = $this->connect->get('proxy');
            $valid = $proxy_list->proxy_cheker($proxy);
            var_dump($valid);
        } while (!$valid);

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
            $this->connect->set('tasks', json_encode((array)['class' => 'QuestionParser', 'url' => $url]));
            exit('Bad connected. Task returned to queue' . PHP_EOL);
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($content);
        return new \DOMXPath($dom);



    }

    /**
     * Parse content
     *
     * @param  string  $url
     */
    public function parse($url)
    {
        $parse_log = new Logger('PARSED');
        $parse_log->pushHandler(new StreamHandler(__DIR__ . '/../log/parse.log', Logger::INFO));

        $xpath = $this->getPage($url);

        foreach ($xpath->query("//td[contains(@class, 'Question')]") as $question) {
            $this->questions_arr[] = $question->textContent;
        }
        foreach ($xpath->query("//td[contains(@class, 'AnswerShort')]") as $answer) {
            $this->answers_arr[] = $answer->textContent;
        }
        $this->insert();
    }

    /**
     * Writes data to the database
     */
    public function insert()
    {
        for ($i = 0; $i < count($this->questions_arr); $i++)
        {
            $id[0] = $this->mysql_connect->check('Questions', $this->questions_arr[$i], 'Question');
            $id[1] = $this->mysql_connect->check('Answers', $this->answers_arr[$i], 'Answer');

            if ($id[0] == false)
            {
                $id[0] = $this->mysql_connect->insert('Questions', $this->questions_arr[$i], 'question');
            }

            if ($id[1] == false)
            {
                $id[1] = $this->mysql_connect->insert('Answers', [
                    'answer' => $this->answers_arr[$i],
                    'length' => $a = iconv_strlen($this->answers_arr[$i]) ], 'answer');
            }

            $this->mysql_connect->insert('QuestionAnswer', [
               'question_id' => $id[0],
               'answer_id' => $id[1]
            ], $type = null);
        }
    }
}