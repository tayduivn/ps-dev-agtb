<?php
interface WebDocument {
	public function uploadDoc($fileToUpload, $docName, $mineType);

    public function downloadDoc($documentId, $documentFormat);
	
	public function shareDoc($documentId, $emails);
	
	public function browseDoc($meeting, $attendeeName);
	
	public function deleteDoc($documentId);

    public function searchDoc($keywords);
}