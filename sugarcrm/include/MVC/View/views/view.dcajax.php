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
/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/MVC/View/views/view.ajax.php');
require_once ('include/DashletContainer/DashletManager.php');

class ViewDcajax extends ViewAjax
{
    /**
     * @see SugarView::display()
     */
    public function display()
    {
     	if(!empty($_REQUEST['dashlet'])){
     		if(!empty($this->bean)){
     					$dash = DashletManager::getDashletFromSubDef($_REQUEST['dashlet'], $this->bean);
     					$dash->setFocusBean($this->bean);
     					$dash->process();
     					
     		}
     		
     		switch($_REQUEST['do']){
     			case 'info':
     				echo json_encode(DashletManager::info($_REQUEST['dashlet']));
     				break;
     			case 'load':
     				$scripts = array();
     				if($dash->hasScript){
     					$scripts[] = 'index.php?dashlet='.$_REQUEST['dashlet'] . '&module=' . $_REQUEST['module'] . '&action=dcajax&do=script&dash_id=' . $dash->id;
     				}
     				echo json_encode(array('id'=>$dash->id, 'html'=>$dash->display(), 'scripts'=>$scripts));
     				break;
     			case 'script':
     				$dash->id = $_REQUEST['dash_id'];
     				echo str_replace(array('<script>', '</script>'), '', $dash->displayScript());
     				break;
     			case 'create':
     				require_once('modules/Home/SubpanelCreates.php');
     				break;
     			default:
     				echo 'No Dashlet Found';
     		}
     	}   
    }
}
