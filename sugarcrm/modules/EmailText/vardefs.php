<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Class for separate storage of Email texts
 */
global $dictionary;
if(file_exists('custom/metadata/emails_beansMetaData.php')) {
  require_once('custom/metadata/emails_beansMetaData.php');
} else {
  require_once('metadata/emails_beansMetaData.php');
}
$dictionary['EmailText'] = $dictionary['emails_text'];