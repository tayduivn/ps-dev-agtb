<?php

global $portal;

$result = $portal->getAttachment($_REQUEST['id']);

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-type: application/force-download");
header("Content-Length: " . filesize($local_location));
header("Content-disposition: attachment; filename=\"".$result['note_attachment']['filename']."\";");
header("Pragma: ");
header("Expires: 0");
set_time_limit(0);

ob_clean();
ob_start();
echo base64_decode($result['note_attachment']['file']);
ob_flush();

?>