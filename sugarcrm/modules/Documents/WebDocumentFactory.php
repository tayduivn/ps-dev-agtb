<?php

require_once('WebDocument.php');
require_once('GoogleDocument.php');

class WebDocumentFactory {

   static function getInstance($type, $url, $name, $password) {
      $instance = new $type($url, $name, $password);
      return $instance;
   }
   
   static function getDocClass($doc_type){
		if($doc_type == 'Google')
		return 'GoogleDocument';
		else
		return 'Sugar';
   }
}
