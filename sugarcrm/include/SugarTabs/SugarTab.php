<?php
//FILE SUGARCRM flav=int ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 
// $Id: SugarTab.php 45763 2009-04-01 19:16:18Z majed $


if(empty($GLOBALS['sugar_smarty']))$GLOBALS['sugar_smarty'] = new Sugar_Smarty();
class SugarTab{
    
    function SugarTab($type='singletabmenu'){
        $this->type = $type;
        
    }
    
    function setup($mainTabs, $otherTabs=array(), $subTabs=array(), $selected_group='All'){
    	// TODO - prefs here
        //_pp($subTabs);_pp($otherTabs);
        $max_subtabs = $GLOBALS['current_user']->getPreference('max_subtabs');
        if($max_subtabs <= 0) $max_subtabs = 12;
        $max_tabs = $GLOBALS['current_user']->getPreference('max_tabs');
        if($max_tabs <= 0) $max_tabs = 12;
        $GLOBALS['sugar_smarty']->assign('sugartabs', array_slice($mainTabs, 0, $max_tabs));
        $GLOBALS['sugar_smarty']->assign('subtabs', array_slice($subTabs, 0, $max_subtabs));
        $GLOBALS['sugar_smarty']->assign('moreMenu', array_slice($mainTabs, $max_tabs));
        $GLOBALS['sugar_smarty']->assign('moreSubMenuName', $selected_group);
        $GLOBALS['sugar_smarty']->assign('moreSubMenu', array_slice($subTabs, $max_subtabs));
        $otherMoreTabs = array();
        if(!empty($otherTabs))
        {
            foreach($otherTabs as $key => $ot)
            {
            	$otherMoreTabs[$key] = array('key' => $key,
                                             'tabs' => array_slice($ot['tabs'], $max_subtabs));
                $otherTabs[$key]['tabs'] = array_slice($ot['tabs'], 0, $max_subtabs);
            }
        }
        else
        {
            $otherMoreTabs[$selected_group] = array('key' => $selected_group,
                                                    'tabs' => array_slice($subTabs, $max_subtabs));
            $otherTabs[$selected_group]['tabs'] = array_slice($subTabs, 0, $max_subtabs);
        }
        //_pp($otherMoreTabs);
        $GLOBALS['sugar_smarty']->assign('othertabs', $otherTabs);
        $GLOBALS['sugar_smarty']->assign('otherMoreSubMenu', $otherMoreTabs);
        $GLOBALS['sugar_smarty']->assign('startSubPanel', $selected_group);
        if(!empty($mainTabs))
        {
            $mtak = array_keys($mainTabs);
            $GLOBALS['sugar_smarty']->assign('moreTab', $mainTabs[$mtak[min(count($mtak)-1, $max_tabs-1)]]['label']);
        }
    }
    
    function fetch(){
        return $GLOBALS['sugar_smarty']->fetch('include/SugarTabs/tpls/' . $this->type . '.tpl');
    }
    function display(){
       $GLOBALS['sugar_smarty']->display('include/SugarTabs/tpls/' . $this->type . '.tpl');
    }
    
    
}



?>
