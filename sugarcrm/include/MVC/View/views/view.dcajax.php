<?php
//FILE SUGARCRM flav=int ONLY
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
