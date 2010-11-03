<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('modules/Boxnet/BoxNet.php');
require_once('include/MVC/View/views/view.detail.php');
class ViewBoxnet extends ViewDetail{
	function __construct(){
		parent::ViewDetail();
	}
	
	function generateBoxNetCode(){
		$bnet = new BoxNet();
		$html = <<<BOXNET
<div class='formHeader'>
<h3 ><span><span style="display:;" id="show_link_boxnet"><a onclick="toggleDisplay('boxnet');document.getElementById('show_link_boxnet').style.display='none';document.getElementById('hide_link_boxnet').style.display='';return false;" class="utilsLink" href="#"><img width="8" height="8" border="0 align=" absmiddle="" alt="Show" src="themes/default/images/advanced_search.gif"/></a></span><span style="display:none" id="hide_link_boxnet"><a onclick="toggleDisplay('boxnet');document.getElementById('hide_link_boxnet').style.display='none';document.getElementById('show_link_boxnet').style.display='';return false;" class="utilsLink" href="#"><img width="8" height="8" border="0" align="absmiddle" alt="Hide" src="themes/default/images/basic_search.gif"/> </a></span>Box.net</span></h3>
<div id='boxnet' style='display:none'>
BOXNET;
$html .=  $bnet->display() . '</div></div>';
		return  $html;
			
	}
	
	function displaySubPanels()
    {

    	echo $this->generateBoxNetCode();
		parent::displaySubPanels();
		
	}
	
	protected function _displaySubPanels()
    {

    	echo $this->generateBoxNetCode();
		parent::_displaySubPanels();
		
	}
	
	
}
