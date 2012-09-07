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
/*********************************************************************************
 * $Id: view.detail.php 
 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Calls module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.detail.php');

class QuotesViewDetail extends ViewDetail 
{
    /**
 	 * @see SugarView::display()
 	 */
 	public function display() 
 	{
		global $beanFiles;
		require_once($beanFiles['Quote']);
		require_once($beanFiles['TaxRate']);
		require_once($beanFiles['Shipper']);

		$this->bean->load_relationship('product_bundles');
		$product_bundle_list = $this->bean->get_linked_beans('product_bundles','ProductBundle');
		if(is_array($product_bundle_list)){

			$ordered_bundle_list = array();
            foreach ($product_bundle_list as $id => $bean)
            {
                $index = $bean->get_index($this->bean->id);
				$ordered_bundle_list[(int)$index[0]['bundle_index']] = $bean;
			} //for
			ksort($ordered_bundle_list);
		} //if

		$this->ss->assign('ordered_bundle_list', $ordered_bundle_list);
		
		$currency = new Currency();
		$currency->retrieve($this->bean->currency_id);
		$this->ss->assign('CURRENCY_SYMBOL', $currency->symbol);
		$this->ss->assign('CURRENCY', $currency->iso4217);
		$this->ss->assign('CURRENCY_ID', $currency->id);
 		
 		if(!(strpos($_SERVER['HTTP_USER_AGENT'],'Mozilla/5') === false)) {
			$this->ss->assign('PDFMETHOD', 'POST');
		} else {
			$this->ss->assign('PDFMETHOD', 'GET');
		}
		
		global $app_list_strings, $current_user;
		$this->ss->assign('APP_LIST_STRINGS', $app_list_strings);
		$this->ss->assign('gridline', $current_user->getPreference('gridline') == 'on' ? '1' : '0');

 		parent::display();
		
 	}
}

