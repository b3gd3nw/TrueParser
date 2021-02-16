<?php

namespace App\Controllers;

 use App\Core\Connect;

 class ProxyController extends Connect
 {
     public function init()
     {
         var_dump(3);
         if(!$this->connect->get('proxy'))
         {
             $this->proxy_grabber();
         }

     }

     public function proxy_grabber()
     {
         $file_handle = fopen(dirname(__DIR__) . '/proxy_list', "r");
         while (!feof($file_handle)) {
             $line = fgets($file_handle);
             $this->connect->set('proxy', $line);
         }
         fclose($file_handle);
     }

     public function proxy_cheker($prx)
     {
         $proxy=  explode(':', $prx);
         $host = $proxy[0];
         $port = $proxy[1];
         $waitTimeoutInSeconds = 10;
         if($fp = @fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)){
             fclose($fp);
             return true;
         } else {
             fclose($fp);
             return false;
         }

     }
 }