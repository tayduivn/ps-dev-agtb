<?php
//FILE SUGARCRM flav=int ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once("modules/Emails/EmailUI.php");

$dir = sugar_cached("modules/Emails/");
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
