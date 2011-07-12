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
			$fs[] = array ('g' => 'aW5jbHVkZS9pbWFnZXMvcG93ZXJlZGJ5X3N1Z2FyY3JtLnBuZw==', 'm' => 'f3ad3d8f733c7326a8affbdc94a2e707', 'a' => '', 'i' => 0 ,'c'=>$case, 'l'=>$level, 's'=>1);
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
    preg_match('/module=([^&]*)/i', $url, $match);

    if(!empty($sugar_config['disableAjaxUI'])){
        return $url;
    }
    else if(isset($match[1]) && in_array($match[1], ajaxBannedModules())){
        return $url;
    }
    else{
        return "#ajaxUILoc=" . urlencode($url);
    }
}

?>
