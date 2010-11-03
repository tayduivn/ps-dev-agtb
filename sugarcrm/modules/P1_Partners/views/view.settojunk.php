<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
/*********************************************************************************

 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');

class P1_PartnersViewSetToJunk extends SugarView
{
    /**
     * Constructor
     */
	public function __construct()
        {
                parent::SugarView();
        }

        function process() {
                $this->display();
        }

        function display()
        {
                if(empty($_POST['mass']) || !isset($_POST['mass'])) {
                        sugar_die('Error 1201: No opportunities are selected. Please select the opportunities from the oppQ module list view');
                }

                foreach($_POST['mass'] as $opportunity_id) {
                        $opportunity = new Opportunity();
                        $opportunity->retrieve($opportunity_id);
                        $opportunity->sales_stage = 'Closed Lost';
                        $opportunity->closed_lost_reason_c = 'Unable To Contact';
			$opportunity->closed_lost_reason_detail_c = 'Invalid Data';
                        $opportunity->closed_lost_description = 'Invalid/Junk Data';
                        $opportunity->save();
                }

                header("Location: index.php?module=P1_Partners&action=index");
        }
}
?>

