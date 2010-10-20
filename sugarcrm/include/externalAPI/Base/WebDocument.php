<?php
interface WebDocument {
	public function uploadDoc($fileToUpload, $docName, $mineType);

    public function downloadDoc($documentId, $documentFormat);
	
	public function shareDoc($documentId, $emails);
	
	public function browseDoc($path);
	
	public function deleteDoc($documentId);

    public function searchDoc($keywords);
}