<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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
 * Description: This file is used to override the default Meta-data DetailView behavior
 * to provide customization specific to the Campaigns module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class OpportunitiesViewEdit extends ViewEdit
{

    public function __construct()
    {
        parent::__construct();
        $this->useForSubpanel = true;
    }

    /**
     * @deprecated
     */
    public function OpportunitiesViewEdit()
    {
        $this->__construct();
    }

    public function display()
    {
        global $app_list_strings;
        $json = getJSONobj();
        $prob_array = $json->encode($app_list_strings['sales_probability_dom']);
        $prePopProb = '';
        if (empty($this->bean->id) && empty($_REQUEST['probability'])) {
            $prePopProb = 'document.getElementsByName(\'sales_stage\')[0].onchange();';
        }
        //BEGIN SUGARCRM flav=pro ONLY
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        $wonStages = $json->encode($settings['sales_stage_won']);
        //END SUGARCRM flav=pro ONLY

        $probability_script = <<<EOQ
	<script>
	prob_array = $prob_array;
	var sales_stage = document.getElementsByName('sales_stage')[0];
	if(sales_stage) {

        var probability = document.getElementsByName('probability')[0];
        //BEGIN SUGARCRM flav=pro ONLY
        won_stages = $wonStages;
        var best_case = document.getElementsByName('best_case')[0];
        var worst_case = document.getElementsByName('worst_case')[0];
        var amount = document.getElementsByName('amount')[0];

        if(won_stages.indexOf(sales_stage.value) > -1) {
            if(best_case) {
                best_case.value = amount.value;
                best_case.setAttribute("readonly", "true");
            }
            if(worst_case) {
                worst_case.value = amount.value;
                worst_case.setAttribute("readonly", "true");
            }
        }
        //END SUGARCRM flav=pro ONLY
        sales_stage.onchange = function() {
            if(typeof(sales_stage.value) != "undefined"
                && prob_array[sales_stage.value]
                && typeof(probability) != "undefined"
            ) {
                probability.value = prob_array[sales_stage.value];
                SUGAR.util.callOnChangeListers(probability);
            }
        //BEGIN SUGARCRM flav=pro ONLY
            if(won_stages.indexOf(sales_stage.value) > -1) {
                if(best_case) {
                    best_case.value = amount.value;
                    best_case.setAttribute("readonly", "true");
                }
                if(worst_case) {
                    worst_case.value = amount.value;
                    worst_case.setAttribute("readonly", "true");
                }
            } else if(typeof(sales_stage.value) != "undefined") {
                if(best_case) {
                    best_case.removeAttribute("readonly");
                }
                if(worst_case) {
                    worst_case.removeAttribute("readonly");
                }
            }
        };
        amount.onchange = function() {
            if(won_stages.indexOf(sales_stage.value) > -1) {
                if(best_case) {
                    best_case.value = amount.value;
                }
                if(worst_case) {
                    worst_case.value = amount.value;
                }
            }
        //END SUGARCRM flav=pro ONLY
        };
	}
	$prePopProb
	</script>
EOQ;

        $this->ss->assign('PROBABILITY_SCRIPT', $probability_script);
        parent::display();
    }
}
