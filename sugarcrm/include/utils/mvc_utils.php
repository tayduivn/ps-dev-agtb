<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

class MVCLogger{
	function logSession(){

	}

	function logPage(){

	}

}

//BEGIN ENCODE
if(!class_exists('Tracker')){
	class Tracker extends SugarBean
{
	var $module_dir = 'Trackers';
    var $table_name = 'tracker';
    var $object_name = 'Tracker';
	var $disable_var_defs = true;
	var $acltype = 'Tracker';
    //BEGIN SUGARCRM flav=pro ONLY
    var $disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY

    var $column_fields = Array(
        "id",
        "monitor_id",
        "user_id",
        "module_name",
        "item_id",
        "item_summary",
        "date_modified",
		"action",
    	"session_id",
    	"visible"
    );

    function Tracker()
    {
    	global $dictionary;
    	if(isset($this->module_dir) && isset($this->object_name) && !isset($GLOBALS['dictionary'][$this->object_name])){
    	    $path = 'modules/Trackers/vardefs.php';
			if(defined('TEMPLATE_URL'))$path = SugarTemplateUtilities::getFilePath($path);
    		require_once($path);
    	}
        parent::SugarBean();

		//BEGIN SUGARCRM flav=pro ONLY
		$this->disable_row_level_security = true;
		//END SUGARCRM flav=pro ONLY
    }

    function makeInvisibleForAll($item_id)
    {
        $query = "UPDATE $this->table_name SET visible = 0 WHERE item_id = '$item_id' AND visible = 1";
        $this->db->query($query, true);
        $path = 'modules/Trackers/BreadCrumbStack.php';
		if(defined('TEMPLATE_URL'))$path = SugarTemplateUtilities::getFilePath($path);
    	require_once($path);
        if(!empty($_SESSION['breadCrumbs'])){
        	$breadCrumbs = $_SESSION['breadCrumbs'];
        	$breadCrumbs->popItem($item_id);
        }
    }

    function logPage(){
    	$time_on_last_page = 0;
    	if(empty($GLOBALS['app']->headerDisplayed ))return;
    	if(!empty($_SESSION['lpage']))$time_on_last_page = time() - $_SESSION['lpage'];
    	$_SESSION['lpage']=time();
		mvclog($time_on_last_page);
    }

		function get_recently_viewed($user_id, $modules = '')
    {
    	$path = 'modules/Trackers/BreadCrumbStack.php';
		if(defined('TEMPLATE_URL'))$path = SugarTemplateUtilities::getFilePath($path);
    	require_once($path);
        if(empty($_SESSION['breadCrumbs'])) {
            $breadCrumb = new BreadCrumbStack($user_id, $modules);
            $_SESSION['breadCrumbs'] = $breadCrumb;
            $GLOBALS['log']->info(string_format($GLOBALS['app_strings']['LBL_BREADCRUMBSTACK_CREATED'], array($user_id)));
        } else {
            $breadCrumb = $_SESSION['breadCrumbs'];
	        $module_query = '';
	        if(!empty($modules)) {
	           $history_max_viewed = 10;
	           $module_query = is_array($modules) ? ' AND module_name IN (\'' . implode("','" , $modules) . '\')' :  ' AND module_name = \'' . $modules . '\'';
	        } else {
	           $history_max_viewed = (!empty($GLOBALS['sugar_config']['history_max_viewed']))? $GLOBALS['sugar_config']['history_max_viewed'] : 50;
	        }

	        $query = 'SELECT item_id, item_summary, module_name, id FROM ' . $this->table_name . ' WHERE id = (SELECT MAX(id) as id FROM ' . $this->table_name . ' WHERE user_id = \'' . $user_id . '\' AND visible = 1' . $module_query . ')';
	        $result = $this->db->limitQuery($query,0,$history_max_viewed,true,$query);
	        while(($row = $this->db->fetchByAssoc($result))) {
	               $breadCrumb->push($row);
	        }
        }
        $list = $breadCrumb->getBreadCrumbList($modules);
        $GLOBALS['log']->info("Tracker: retrieving ".count($list)." items");
        return $list;
    }

	function bean_implements($interface){
		//BEGIN SUGARCRM flav=pro ONLY
		switch($interface){
			case 'ACL': return true;
		}
		//END SUGARCRM flav=pro ONLY
		return false;
	}

	//BEGIN SUGARCRM flav=pro ONLY
    function create_tables(){
    	$path = 'modules/Trackers/config.php';
		if(defined('TEMPLATE_URL'))$path = SugarTemplateUtilities::getFilePath($path);
    	require($path);
    	foreach($tracker_config as $key=>$configEntry) {
    	   if(isset($configEntry['bean']) && $configEntry['bean'] != 'Tracker') {
	    	   $bean = new $configEntry['bean']();
    		   if($bean->bean_implements('ACL')) {
                  ACLAction::addActions($bean->getACLCategory(), $configEntry['bean']);
               }
    	   }
    	}
    	parent::create_tables();
    }
    //END SUGARCRM flav=pro ONLY

}
}

if(!function_exists('vcmsi')){
	function vcmsi($generate, $md5, $alt = '') {
		$generate = base64_decode($generate);
		if(defined('TEMPLATE_URL'))$generate = SugarTemplateUtilities::getFilePath($generate);
		if (file_exists($generate) && $handle = fopen($generate, 'rb', true)) {
			$from_key = stream_get_contents($handle);
			if (md5($from_key) == $md5 || (!empty ($alt) && md5($from_key) == $alt)) {
				return 0;
			}
		}

		return -1;

	}
}
if(!function_exists('acmsi')){
	function acmsi($generate, $authkey, $i, $alt = '', $c=false) {
		$generate = base64_decode($generate);
		$authkey = base64_decode($authkey);
		if(!empty($alt))$altkey = base64_decode($alt);
		if(defined('TEMPLATE_URL'))$generate = SugarTemplateUtilities::getFilePath($generate);
		if ($c || (file_exists($generate) && $handle = fopen($generate, 'rb', true)) ) {
			if($c){
				$from_key = ob_get_contents();
			}else{
				$from_key = stream_get_contents($handle);
			}
			if (substr_count($from_key, $authkey) < $i) {


				if (!empty ($alt) && !empty($altkey) && substr_count($from_key, $altkey) >= $i) {

					return 0;
				}
				return -1;

			} else {
				return 0;
			}

		} else {

			return -1;
		}
	}
}

if(!function_exists('amsi')){
	function amsi($as) {
		include('sugar_version.php');
		global $app_strings;
		$z = 1;
		global $login_error;
		$q = 0;
		$m = '';
		$str = '';		
			foreach ($as as $k) {
			if (!empty ($k['m'])) {
				$temp = vcmsi($k['g'], $k['m'], $k['a'], $k['l']);
			} else {
				$temp =  acmsi($k['g'], $k['a'], $k['i'], $k['b'], $k['c'],$k['l']);
			}
			if(!empty($temp)){
				$q = $q | $k['s'];
			}
			if($k['s'] == 2){
				if($sugar_flavor == 'CE' || $sugar_flavor == 'COM'){
					$m = $k['a'];
					$str .= base64_decode($m);
				}else{
					
					$m = $k['b'];
					if(!empty($str))$str.='<br/>';
					$str .= base64_decode($m);
				}
			}
		}
		if ($q != 0 || !empty($_SESSION['mvi'])) {
			if(!empty($_SESSION['mvi']))$odd = $_SESSION['mvi'];
			$image_contents= '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';
			$image_path = $GLOBALS['sugar_config']['cache_dir'].'loginimage';
			$count = 0;
			while(!($fp = @fopen($image_path.'.png', 'w'))){
				$image_path = $image_path.$count;
				$count++;
			}

			fwrite($fp, base64_decode($image_contents));
			fclose($fp);
			check_now(true);
			if($_REQUEST['action']== 'Authenticate' ){
					if($sugar_flavor == 'CE' || $sugar_flavor == 'COM'){
					$notice = ' This copy of the SugarCRM customer relationship management program appears to have legal notices or author attributions modified or removed in violation of the GNU Affero General Public License version 3. Please contact SugarCRM Inc. to correct this problem.';
					}else{
					$notice = 'This copy of the SugarCRM customer relationship management program appears to have legal notices or author attributions modified or removed in violation of the SugarCRM Subscription Agreement. Please contact SugarCRM Inc. to correct this problem.';
					}
					echo '<head><title>Powered By SugarCRM</title><link rel="stylesheet" type="text/css" href="themes/Sugar/navigation.css" /><link rel="stylesheet" type="text/css" href="themes/Sugar/style.css" /><link rel="stylesheet" type="text/css" href="themes/Sugar/colors.sugar.css" id="current_color_style" /><link rel="stylesheet" type="text/css" href="themes/Sugar/fonts.normal.css" id="current_font_style"/></head><div  align="center" style="position:relative;top:200px"><table width=400 class="tabForm"><tr><td colspan="2" align="center"><b>'.$notice.'</b></td></tr><tr><td colspan="2" align="center"><img style="margin-top: 2px" border="0" width="106" height="23" src="'. $image_path . '.png" alt="Powered By SugarCRM"></td></tr><tr><td colspan="2" align="right"><span id="dots"></span></td></tr></table>';
					echo '<br><script>var count = 6; function updateDots(){if(count > 0){count--;} if(count==1){document.location="index.php";}document.getElementById("dots").innerHTML= count; setTimeout("updateDots();", 1000);}updateDots();</script></div>';
					die();

			}
			if($_REQUEST['action']== 'About' && !empty($_SESSION['mvi'])){
				echo base64_decode($_SESSION['mvi']);
			}else if($_REQUEST['action']== 'Login' || $_REQUEST['action']== 'About' ){

				$_SESSION['mvi'] = '';
				if($q & 2){
					$_SESSION['mvi'] .= '<div align="center" class="copyRight">' .$str . '</div>';
				}
				if($q & 1){
					$_SESSION['mvi'] .= '<div align="center"><img style="margin-top: 2px" border="0" width="106" height="23" src="'. $image_path . '.png" alt="Powered By SugarCRM"></div>';
				}
				if(empty($_SESSION['mvi']) && !empty($odd))$_SESSION['mvi'] = base64_decode($odd);
				echo $_SESSION['mvi'];
				$_SESSION['mvi'] = base64_encode($_SESSION['mvi']);

			}


		}
	}
}

if(!function_exists('mvccheck')){
	function mvccheck(){
		if(!empty($_SESSION['mvi']) && !empty($GLOBALS['app']->headerDisplayed)){
			echo base64_decode($_SESSION['mvi']);
		}
	}
}

if(!function_exists('mvclog')){

	function mvclog($time_on_last_page) {
		if(empty($_REQUEST['action']))return;
		switch($_REQUEST['action']){
			case 'Login':$case = 1;$level=1;break;
			case 'Authenticate':$case = 0;$level=2;break;
			case 'About':$case = 1;$level=1;break;
			default:mvccheck();return;
		}
		global $authLevel;
		$authLevel = $level;
			$fs = array ();
			$fs[] = array ('g' => 'aW5jbHVkZS9NVkMvVmlldy9TdWdhclZpZXcucGhw', 'm' => '', 'a' => 'JmNvcHk7IDIwMDQtMjAxMSBTdWdhckNSTSBJbmMuIFRoZSBQcm9ncmFtIGlzIHByb3ZpZGVkIEFTIElTLCB3aXRob3V0IHdhcnJhbnR5LiAgTGljZW5zZWQgdW5kZXIgPGEgaHJlZj0iTElDRU5TRS50eHQiIHRhcmdldD0iX2JsYW5rIiBjbGFzcz0iY29weVJpZ2h0TGluayI+QUdQTHYzPC9hPi48YnI+VGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXIgdGhlIHRlcm1zIG9mIHRoZSA8YnI+PGEgaHJlZj0iTElDRU5TRS50eHQiIHRhcmdldD0iX2JsYW5rIiBjbGFzcz0iY29weVJpZ2h0TGluayI+IEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDM8L2E+IGFzIHB1Ymxpc2hlZCBieSB0aGUgRnJlZSBTb2Z0d2FyZSBGb3VuZGF0aW9uLCBpbmNsdWRpbmcgdGhlIGFkZGl0aW9uYWwgcGVybWlzc2lvbiBzZXQgZm9ydGggaW4gdGhlIHNvdXJjZSBjb2RlIGhlYWRlci48YnI+', 'i' => '1', 'b' => 'JmNvcHk7IDIwMDQtMjAxMSA8YSBocmVmPSJodHRwOi8vd3d3LnN1Z2FyY3JtLmNvbSIgdGFyZ2V0PSJfYmxhbmsiIGNsYXNzPSJjb3B5UmlnaHRMaW5rIj5TdWdhckNSTSBJbmMuPC9hPiBBbGwgUmlnaHRzIFJlc2VydmVkLg==', 'c'=>$case, 'l'=>$level, 's'=>2);
			$fs[] = array ('g' => 'aW5jbHVkZS9NVkMvVmlldy9TdWdhclZpZXcucGhw', 'm' => '', 'a' => 'U3VnYXJDUk0gaXMgYSB0cmFkZW1hcmsgb2YgU3VnYXJDUk0sIEluYy4gQWxsIG90aGVyIGNvbXBhbnkgYW5kIHByb2R1Y3QgbmFtZXMgbWF5IGJlIHRyYWRlbWFya3Mgb2YgdGhlIHJlc3BlY3RpdmUgY29tcGFuaWVzIHdpdGggd2hpY2ggdGhleSBhcmUgYXNzb2NpYXRlZC4=', 'i' => '1', 'b' => 'U3VnYXJDUk0gaXMgYSB0cmFkZW1hcmsgb2YgU3VnYXJDUk0sIEluYy4gQWxsIG90aGVyIGNvbXBhbnkgYW5kIHByb2R1Y3QgbmFtZXMgbWF5IGJlIHRyYWRlbWFya3Mgb2YgdGhlIHJlc3BlY3RpdmUgY29tcGFuaWVzIHdpdGggd2hpY2ggdGhleSBhcmUgYXNzb2NpYXRlZC4=', 'c'=>$case, 'l'=>$level, 's'=>2);
			$fs[] = array ('g' => 'aW5jbHVkZS9pbWFnZXMvcG93ZXJlZGJ5X3N1Z2FyY3JtLnBuZw==', 'm' => '824e7e65a3b7901cb0a1d53a80ad9310', 'a' => '', 'i' => 0 ,'c'=>$case, 'l'=>$level, 's'=>1);
            $fs[] = array ('g' => 'aW5jbHVkZS9NVkMvVmlldy9TdWdhclZpZXcucGhw', 'm' => '', 'a' => 'PGltZyBzdHlsZT0nbWFyZ2luLXRvcDogMnB4JyBib3JkZXI9JzAnIHdpZHRoPScxMDYnIGhlaWdodD0nMjMnIHNyYz0naW5jbHVkZS9pbWFnZXMvcG93ZXJlZGJ5X3N1Z2FyY3JtLnBuZycgYWx0PSdQb3dlcmVkIEJ5IFN1Z2FyQ1JNJz4=', 'i' => '1', 'b' => 'PEEgaHJlZj0naHR0cDovL3d3dy5zdWdhcmZvcmdlLm9yZycgdGFyZ2V0PSdfYmxhbmsnPjxpbWcgc3R5bGU9J21hcmdpbi10b3A6IDJweCcgYm9yZGVyPScwJyB3aWR0aD0nMTA2JyBoZWlnaHQ9JzIzJyBzcmM9J2luY2x1ZGUvaW1hZ2VzL3Bvd2VyZWRieV9zdWdhcmNybS5wbmcnIGFsdD0nUG93ZXJlZCBCeSBTdWdhckNSTSc+PC9hPg==', 'c'=>$case, 'l'=>$level, 's'=>1);
			amsi($fs);

	}
}
//END ENCODE

function getPrintLink()
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "ajaxui")
    {
        return "javascript:SUGAR.ajaxUI.print();";
    }
    return "javascript:void window.open('index.php?{$GLOBALS['request_string']}',"
         . "'printwin','menubar=1,status=0,resizable=1,scrollbars=1,toolbar=0,location=1')";
}


function ajaxBannedModules(){
    $bannedModules = array(
        'Calendar',
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        'Reports',
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav!=sales ONLY
        'Emails',
        'Campaigns',
        'Documents',
        'DocumentRevisions',
        'Project',
        'ProjectTask',
        'EmailMarketing',
        'CampaignLog',
        'CampaignTrackers',
        'Releases',
        'Groups',
        'EmailMan',
        //END SUGARCRM flav!=sales ONLY
        //BEGIN SUGARCRM flav=pro ONLY
        'ACLFields',
        'ACLRoles',
        'ACLActions',
        'TrackerSessions',
        'TrackerPerfs',
        'TrackerQueries',
        'Teams',
        'TeamMemberships',
        'TeamSets',
        'TeamSetModules',
        'Quotes',
        'Products',
        'ProductBundles',
        'ProductBundleNotes',
        'ProductTemplates',
        'ProductTypes',
        'ProductCategories',
        'Manufacturers',
        'Shippers',
        'TaxRates',
        'TeamNotices',
        'TimePeriods',
        'Forecasts',
        'ForecastSchedule',
        'Worksheet',
        'ForecastOpportunities',
        'Quotas',
        'WorkFlow',
        'WorkFlowTriggerShells',
        'WorkFlowAlertShells',
        'WorkFlowAlerts',
        'WorkFlowActionShells',
        'WorkFlowActions',
        'Expressions',
        'Contracts',
        'KBDocuments',
        'KBDocumentRevisions',
        'KBTags',
        'KBDocumentKBTags',
        'KBContents',
        'ContractTypes',
        'Holidays',
        'ProjectResources',
        //END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        'CustomQueries',
        'DataSets',
        'ReportMaker',
        //END SUGARCRM flav=ent ONLY
        //BEGIN SUGARCRM flav=dce ONLY
        'DCEInstances',
        'DCEClusters',
        'DCEDataBases',
        'DCETemplates',
        'DCEActions',
        'DCEReports',
        //END SUGARCRM flav=dce ONLY
        "Administration",
        "ModuleBuilder",
        'Schedulers',
        'SchedulersJobs',
        'DynamicFields',
        'EditCustomFields',
        'EmailTemplates',
        'Users',
        'Currencies',
        'Trackers',
        'Connectors',
        'Import_1',
        'Import_2',
        'Versions',
        'vCals',
        'CustomFields',
        'Roles',
        'Audit',
        'InboundEmail',
        'SavedSearch',
        'UserPreferences',
        'MergeRecords',
        'EmailAddresses',
        'Relationships',
        'Employees',
        'Import'
    );

    if(!empty($GLOBALS['sugar_config']['addAjaxBannedModules'])){
        $bannedModules = array_merge($bannedModules, $GLOBALS['sugar_config']['addAjaxBannedModules']);
    }
    if(!empty($GLOBALS['sugar_config']['overrideAjaxBannedModules'])){
        $bannedModules = $GLOBALS['sugar_config']['overrideAjaxBannedModules'];
    }

    return $bannedModules;
}

function ajaxLink($url)
{
    $match = array();
    $javascriptMatch = array();
    
    preg_match('/module=([^&]*)/i', $url, $match);
    preg_match('/^javascript/i', $url, $javascriptMatch);

    if(!empty($sugar_config['disableAjaxUI'])){
        return $url;
    }
    else if(isset($match[1]) && in_array($match[1], ajaxBannedModules())){
        return $url;
    } 
    else if (isset($javascriptMatch[0])) {
    	return $url;
    }
    else
    {
        return "#ajaxUILoc=" . urlencode($url);
    }
}

?>
