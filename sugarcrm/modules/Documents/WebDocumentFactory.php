<?php

require_once('WebDocument.php');
require_once('GoogleDocument.php');

class WebDocumentFactory {

   static function getInstance($type, $url, $name, $password) {
      $instance = new $type($url, $name, $password);
      return $instance;
   }
   
   static function getDocClass($doc_type){
   		switch($doc_type) {
   			case 'Google':
   				return 'GoogleDocument';
   				break;
   			case 'Box.net':
   				return 'BoxDocument';
   				break;
   			case 'LotusLive':
   				return "LotusDocument";
   				break;
   			default:
   				return 'Sugar';
   		}
   }
   
   static function getEapmType($doc_type) {
       switch($doc_type) {
       	case 'Google':
       		return 'google';
       		break;
       	case 'Box.net':
       		return 'box';
       		break;
       	case 'LotusLive':
       		return 'lotuslive';
       		break;
       	default:
       		return 'Sugar';
       }   	
   }
}
