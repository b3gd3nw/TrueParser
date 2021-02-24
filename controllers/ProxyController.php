<?php

namespace App\Controllers;

 use App\Core\Connect;

 class ProxyController extends Connect
 {

     /**
      * Checking and getting a list of proxies
      */
     public function init()
     {
         if($this->connect->get('proxy') == null)
         {
             $this->proxy_grabber();
         }

     }

     /**
      * Check proxy to open socket
      *
      * @param  string  $prx
      * @return bool
      */
     public function proxy_cheker($prx)
     {
         $proxy=  explode(':', $prx);
         $host = $proxy[0];
         $port = $proxy[1];
         $waitTimeoutInSeconds = 10;
         if($fp = @fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)){
             return true;
         } else {
             return false;
         }
     }

     /**
      * Parse proxy from the specified link
      */
     public function proxy_grabber()
     {
         $link = 'https://www.sslproxies.org/';

         $agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36';

         $ch = curl_init($link);
         curl_setopt($ch, CURLOPT_USERAGENT, $agent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         $response_data = curl_exec($ch);
         if (curl_errno($ch) > 0) {
             die('Error curl: ' . curl_error($ch));
         }
         curl_close($ch);

         preg_match_all('#<td>[0-9.]{5,}[0-9]{2,}</td><td>[0-9]{2,5}</td>#', $response_data, $rawlist);

         var_dump($rawlist);
         $cleanedList = str_replace('</td><td>', ':', $rawlist[0]);
         var_dump($cleanedList);
         $cleanedList = str_replace('<td>', '', $cleanedList);
         $cleanedList = str_replace('</td>', '', $cleanedList);

         foreach ($cleanedList as $key => $value) {
             $this->connect->set('proxy', $value . PHP_EOL);
         }
     }
 }