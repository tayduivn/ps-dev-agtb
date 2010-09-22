<?php
abstract class WebDocument{
	abstract function uploadDoc($fileToUpload, $docName, $mineType);

    abstract function downloadDoc($documentId, $documentFormat);
	
	abstract function shareDoc($documentId, $emails);
	
	abstract function browseDoc($meeting, $attendeeName);
	
	abstract function deleteDoc($documentId);

    abstract function searchDoc($keywords);
}