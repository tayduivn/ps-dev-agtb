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
