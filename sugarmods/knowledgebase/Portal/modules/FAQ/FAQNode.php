<?php
class FAQNode {
	
var $documents;
var $childNodes;
var $tagId;
var $id;
var $parentId;
var $name;
var $isRoot;

function FAQNode($uid, $aid, $pid, $n, $root = false) {
   $this->id = $uid;
   $this->tagId = $aid;
   $this->parentId = $pid;
   $this->name = $n;
   $this->isRoot = $root;
   $this->childNodes = array();
   $this->documents = array();
}


function addChild($childNode) {
   if(empty($childNodes[$childNode->id])) {
   	  $this->childNodes[$childNode->id] = $childNode;
   }
}

function addDocument($docId) {
   if(!in_array($docId, $this->documents)) {
   	  $this->documents[] = $docId;
   }	
}
	
}
?>
