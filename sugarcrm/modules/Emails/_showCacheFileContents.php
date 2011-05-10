<?php
//FILE SUGARCRM flav=int ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*********************************************************************************
 * $Id: Delete.php,v 1.22 2006/01/17 22:50:52 majed Exp $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/
require_once("modules/Emails/EmailUI.php");

$dir = "{$sugar_config['cache_dir']}modules/Emails/";
$files = findAllFiles($dir, array());
sort($files);
$filter = array(
	'/folders/',
);

$cleanFiles = array();

foreach($files as $file) {
	if(strpos($file, '/folders/') !== false && strpos($file, 'folders.php') === false) {
		$cleanFiles[$file] = str_replace(".imapFetchOverview.php","", substr($file, strpos($file, '/Emails/') + 7));
	}
}
//sort($cleanFiles);

$out  = "<form action='index.php' method='post'>";
$out .= "<input type='hidden' name='module' value='Emails'>";
$out .= "<input type='hidden' name='action' value='_showCacheFileContents'>";
$out .= "<select name='file'>".get_select_options_with_id($cleanFiles, '')."</select>";
$out .= "<input type='submit'>";
$out .= "</form>";
echo $out;


if(isset($_REQUEST['file'])) {
	
	include($_REQUEST['file']); // provides $cacheFile
	/*
	cacheFile = array (
  'timestamp' => 1181945134,
  'imapFetchOverview' => '
  */
	$cache = unserialize($cacheFile['imapFetchOverview']);
	
	echo "<div>";
	echo "timestamp: ".date('r',$cacheFile['timestamp']);
	echo "</div>";
	echo "<div>";
	_pp($cache);
	echo "</div>";
}
