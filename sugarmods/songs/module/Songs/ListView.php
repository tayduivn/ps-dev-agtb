<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: ListView.php 13951 2006-06-12 19:44:03Z awu $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('XTemplate/xtpl.php');
require_once("data/Tracker.php");
require_once('modules/Songs/Song.php');
require_once('themes/'.$theme.'/layout_utils.php');

require_once('include/ListView/ListView.php');



global $app_strings;
global $app_list_strings;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Songs');

global $urlPrefix;

global $currentModule;

global $theme;

if (!isset($where)) $where = "";

$seedSong =& new Song();
require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
}else{
	$storeQuery->saveFromGet($currentModule);
}
if(isset($_REQUEST['query']))
{
	// we have a query
	if (isset($_REQUEST['title'])) $title = $_REQUEST['title'];
	if (isset($_REQUEST['length'])) $length = $_REQUEST['length'];
	if (isset($_REQUEST['comment'])) $comment = $_REQUEST['comment'];
	if (isset($_REQUEST['bitrate'])) $bitrate = $_REQUEST['bitrate'];
	if (isset($_REQUEST['explicit'])) $explicit = $_REQUEST['explicit'];
	if (isset($_REQUEST['genre'])) $genre = $_REQUEST['genre'];
	if (isset($_REQUEST['format'])) $format = $_REQUEST['format'];

	$where_clauses = Array();

	if(isset($title) && $title != "") array_push($where_clauses, "songs.title like '".PearDatabase::quote($title)."%'");
	if(isset($length) && $length != "")	array_push($where_clauses, "songs.length like '".PearDatabase::quote($length)."%'");
	if(isset($comment) && $comment != "") array_push($where_clauses, "songs.comment = '".PearDatabase::quote($comment)."'");
	if(isset($bitrate) && $bitrate != "") array_push($where_clauses, "songs.bitrate like '".PearDatabase::quote($bitrate)."%'");
	if(isset($explicit) && $explicit != "") array_push($where_clauses, "songs.explicit = 1");
	if(isset($genre) && $genre != "") array_push($where_clauses, "songs.genre like '".PearDatabase::quote($genre)."'");
	if(isset($format) && $format != "") array_push($where_clauses, "songs.format like '".PearDatabase::quote($format)."'");

	$seedSong->custom_fields->setWhereClauses($where_clauses);

	$where = "";
	foreach($where_clauses as $clause)
	{
		if($where != "")
		$where .= " and ";
		$where .= $clause;
	}

	$GLOBALS['log']->info("Here is the where clause for the list view: $where");
}

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/Songs/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("IMAGE_PATH", $image_path);
	$search_form->assign("ADVANCED_SEARCH_PNG", get_image($image_path.'advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
	$search_form->assign("BASIC_SEARCH_PNG", get_image($image_path.'basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
	if (isset($first_name)) $search_form->assign("TITLE", $_REQUEST['title']);
	if (isset($last_name)) $search_form->assign("GENRE", $_REQUEST['genre']);
	$search_form->assign("JAVASCRIPT", get_clear_form_js());
	$header_text = '';

	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE'], $header_text, false);

	if (isset($genre)) $search_form->assign("GENRE_OPTIONS", get_select_options_with_id($app_list_strings['song_genre_dom'], $genre));
	else $search_form->assign("GENRE_OPTIONS", get_select_options_with_id($app_list_strings['song_genre_dom'], ''));

	if (isset($_REQUEST['advanced']) && $_REQUEST['advanced'] == 'true') {

		if(isset($title) && $title != "") $search_form->assign("TITLE", $title);
		if(isset($length) && $length != "")	$search_form->assign("LENGTH", $length);
		if(isset($bitrate) && $bitrate != "") $search_form->assign("BITRATE", $bitrate);
		if(isset($explicit) && $explicit != "") $search_form->assign("EXPLICIT", $explicit);

		if (isset($format)) $search_form->assign("FORMAT_OPTIONS", get_select_options_with_id($app_list_strings['song_format_dom'], $format));
		else $search_form->assign("FORMAT_OPTIONS", get_select_options_with_id($app_list_strings['song_format_dom'], ''));


		$seedSong->custom_fields->populateXTPL($search_form, 'search' );

		$search_form->parse("advanced");
		$search_form->out("advanced");
	}
	else
	{
		// adding custom fields:
		$seedSong->custom_fields->populateXTPL($search_form, 'search' );

		$search_form->parse("main");
		$search_form->out("main");
	}

	echo get_form_footer();
	echo "\n<BR>\n";
}


$ListView = new ListView();

$ListView->initNewXTemplate( 'modules/Songs/ListView.html',$current_module_strings);
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE'] );
global $current_user;
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
	$ListView->setHeaderText("<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".get_image($image_path."EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>" );
}
$ListView->setQuery($where, "", "", "SONG");
$ListView->processListView($seedSong, "main", "SONG");
?>
