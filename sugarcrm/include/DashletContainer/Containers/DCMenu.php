<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class DCMenu extends DashletContainer
{
	protected function getMenuItem($module)
	{
	    $imageURL = SugarThemeRegistry::current()->getImageURL("icon_{$module}_bar_32.png");
		$imageName = "icon_{$module}_bar_32.png";
	    if (empty($imageURL))
	    	$imageName = "icon_generic_bar_32.png";
	    $module_mod_strings = return_module_language($GLOBALS['current_language'], $module);
	    if ( isset($module_mod_strings['LNK_NEW_RECORD']) )
	        $createRecordTitle = $module_mod_strings['LNK_NEW_RECORD'];
	    elseif ( isset($module_mod_strings['LNK_NEW_'.strtoupper($GLOBALS['beanList'][$module])]) )
	        $createRecordTitle = $module_mod_strings['LNK_NEW_'.strtoupper($GLOBALS['beanList'][$module])];
	    elseif ( strtoupper($GLOBALS['beanList'][$module]) == 'ACASE' && isset($module_mod_strings['LNK_NEW_CASE']) )
	        $createRecordTitle = $module_mod_strings['LNK_NEW_CASE'];
        else
	        $createRecordTitle = $GLOBALS['app_strings']['LBL_CREATE_BUTTON_LABEL'].' '.$module_mod_strings['LBL_MODULE_NAME'];

	    $image = SugarThemeRegistry::current()->getImage($imageName, "class='icon' alt='{$createRecordTitle}' title='{$createRecordTitle}' id='dcMenu_{$module}_quick_create_icon'");
	    return array('module'=>$module,'createRecordTitle'=>$createRecordTitle, 'image' => $image);

	}

	protected function getDynamicMenuItem($def)
	{
  	if (!empty($def['icon']))
        {
		$imageName = basename($def['icon']);
        } else {
	    	$imageName = "icon_generic_bar_32.png";
        }
        
	    $module = !empty($def['module']) ? $def['module'] : "";
	    $label = isset($def['label']) ? translate($def['label'], $module) : "";
        $modal = isset($def['modal']) ? $def['modal'] : true;
	    $action = isset($def['action']) ? $def['action'] : "DCMenu.menu('$module','$label', $modal); return false;";
	    $script = isset($def['script_url']) ? '<script type="text/javascript" src="' . $def['script_url'] . '"></script>' : "";
		$url = "javascript: $action";
		$image = SugarThemeRegistry::current()->getLink($url, $label, '', $imageName, 'class="icon"');
	    return array('script'=>$script, 'image' => $image);
	}


	public function getSearchIcon() {
		$iconSearchUrl = "javascript: DCMenu.spot(document.getElementById('sugar_spot_search').value);";
		$iconSearch = SugarThemeRegistry::current()->getLink($iconSearchUrl, '', 'class="btn btn-inverse searchIconLink"', "", '',null,null,'','','<i class="icon icon-search"></i>');
		return $iconSearch;
	}
	
	public function getNotifications()
    {
		global $current_user;

        $iconImage = "index.php?entryPoint=download&id=".$current_user->picture."&type=SugarFieldImage&isTempFile=1&isProfile=1";
        $code = '';
        $class = ' class="none"';
		if( is_admin($current_user) )
        {
		    $shouldSkip = sugar_cache_retrieve('dcmenu_check_notifications');
		    $unreadNotifications = 0;
		    if ( $shouldSkip != '1' )
            {
                require_once('modules/Notifications/Notifications.php');
                $n = new Notifications();
                $unreadNotifications = $n->getSystemNotificationsCount();
                if ( $unreadNotifications == 0 )
                    sugar_cache_put('dcmenu_check_notifications','1');
            }

            if($unreadNotifications > 0)
            {
                $code = '<div id="notifCount" class="notifCount">'.$unreadNotifications.'</div>';
                $class = "";
            }

            $image = '<span class="dc_notif_icon" border="0" alt="'.$unreadNotifications.'" style="background-image: url('.$iconImage.');  "></span>';
		}
        else
        {
            $image = '<span class="dc_notif_icon" border="0" style="background-image: url('.$iconImage.');  "></span>';
		}
		
		return array('class'=>$class,'icon'=>$image, 'code'=> $code);	
	}
	
	
	public function getScript() {
		$record = !empty($_REQUEST['record'])?$_REQUEST['record']:'';
		$module = !empty($_REQUEST['module'])?$_REQUEST['module']:'';


		$action = $GLOBALS['app']->controller->action;
		//BEGIN SUGARCRM flav=sugarsurvey ONLY
		$version = urlencode($GLOBALS['sugar_version']);
		$flavor = urlencode($GLOBALS['sugar_flavor']);
		$build = urlencode($GLOBALS['sugar_build']);
		$sugarsurvey = "http://survey-js.sugarcrm.com/SugarSurvey/index.php?do=jssurvey&version={$version}&build={$build}&flavor={$flavor}";
		//END SUGARCRM flav=sugarsurvey ONLY
		$html = getVersionedScript('include/DashletContainer/Containers/DCMenu.js');

        if (!is_file(sugar_cached("include/javascript/sugar_field_grp.js"))) {
            $_REQUEST['root_directory'] = ".";
            require_once("jssource/minify_utils.php");
            ConcatenateFiles(".");
        }
        $html .= getVersionedScript('cache/include/javascript/sugar_field_grp.js');
        $html .= <<<EOQ
		<script>
		YUI({combine: true, timeout: 10000, base:"include/javascript/yui3/build/", comboBase:"index.php?entryPoint=getYUIComboFile&"}).use('node-base', 'event-key', function(Y){
			function init(){
				DCMenu.module = '$module';
				DCMenu.record = '$record';
				DCMenu.action = '$action';
				//BEGIN SUGARCRM flav=notifications ONLY
				setInterval("DCMenu.checkForNewNotifications()",10000);
				//END SUGARCRM flav=notifications ONLY

				// store the return value from Y.on to remove the listener later
    			var handle = Y.on('key', function(e) {
        			DCMenu.spot(document.getElementById('sugar_spot_search').value);
        			e.halt();
				 }, '#sugar_spot_search', 'down:13', Y);


			}
	    	YAHOO.util.Event.onDOMReady(init);

		});
		</script>
		//BEGIN SUGARCRM flav=sugarsurvey ONLY
		<script src='{$sugarsurvey}'></script>
		//END SUGARCRM flav=sugarsurvey ONLY
EOQ;

	return $html;	
	}
	
	public function getLayout() {
		
	}

    /**
     * @return array
     */
	public function getMenus()
	{
        $DCActions = array();

		$actions_path = "include/DashletContainer/Containers/DCActions.php";
		if (is_file('custom/' . $actions_path))
        {
		    include('custom/' . $actions_path);
        }
		else
        {
		    include($actions_path);
        }
        if (is_file('custom/application/Ext/DashletContainer/Containers/dcactions.ext.php'))
			include 'custom/application/Ext/DashletContainer/Containers/dcactions.ext.php';
		
		$filterDCActions = array();
		foreach($DCActions as $action)
        {
			if(ACLController::checkAccess($action, 'edit', true))
            {
			   //BEGIN SUGARCRM flav=sales ONLY
			   $ss_admin_whitelist = getSugarSalesAdminWhiteList();
			   if(!is_admin($GLOBALS['current_user']) || in_array($action, $ss_admin_whitelist))
			   //END SUGARCRM flav=sales ONLY
			   $filterDCActions[] = $this->getMenuItem($action);
			}
		}

        return $filterDCActions;

	}

    /**
     * Get the partner icon menus that are displayed in the bottom right footer.
     *
     * @return array
     */
    public function getPartnerIconMenus()
    {
        $dynamicDCActions = array();
        $filterDynamicDCActions = array();
        $dyn_actions_path = "include/DashletContainer/Containers/DynamicDCActions.php";
        if (is_file('custom/' . $dyn_actions_path))
        {
            include('custom/' . $dyn_actions_path);
        }
        else if ( is_file($dyn_actions_path) )
        {
            include($dyn_actions_path);
        }
        if (is_file('custom/application/Ext/DashletContainer/Containers/dcactions.ext.php'))
        {
            include 'custom/application/Ext/DashletContainer/Containers/dcactions.ext.php';
        }

        foreach($dynamicDCActions as $def)
        {
            $filterDynamicDCActions[] = $this->getDynamicMenuItem($def);
        }

        return $filterDynamicDCActions;
    }
}
