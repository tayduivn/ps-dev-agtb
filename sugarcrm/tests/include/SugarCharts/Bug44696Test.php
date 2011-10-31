<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarCharts/SugarChart.php');
/**
 * Created: Sep 28, 2011
 */
class Bug44696Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $sugarChartObject;
    
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $sugarChartObject = new SugarChart();
        $sugarChartObject->group_by = array ('sales_stage', 'user_name');
        $sugarChartObject->data_set = $this->getDataSet();
        $sugarChartObject->base_url = array ('module' => 'Opportunities',
                                                                                'action' => 'index',
                                                                                'query' => 'true',
                                                                                'searchFormTab' => 'advanced_search');
        $sugarChartObject->url_params = array ();
        $sugarChartObject->is_currency = true;
        // we have 5 users 
        $sugarChartObject->super_set = array ('will', 'max', 'sarah', 'sally', 'chris');
        $this->sugarChartObject = $sugarChartObject;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function getDataSet() {
        return array (
           array (
                'sales_stage' => 'Proposal/Price Quote',
               'user_name' => 'max',
               'assigned_user_id' => 'seed_max_id',
               'opp_count' => '1',
               'total' => '50',
               'key' => 'Proposal/Price Quote',
               'value' => 'Proposal/Price Quote',
               ), 
            array (
                'sales_stage' => 'Proposal/Price Quote',
               'user_name' => 'sally',
               'assigned_user_id' => 'seed_sally_id',
               'opp_count' => '2',
               'total' => '75',
               'key' => 'Proposal/Price Quote',
               'value' => 'Proposal/Price Quote',
               ),
        );
    }
    
    /**
     * We check, that groups with NULL value remain their order in subgroups tag and won't fall down under not null valued groups. 
     * This way we guarantee that links will be put correctly to each user in whole user list (will, max, etc.). 
     */
    public function testCorrectXml() 
    {
        $actual = $this->sugarChartObject->xmlDataGenericChart();
        $expected = $this->compareXml();
        $order   = array("\r\n", "\n", "\r", "\t");
        $replace = "";
        // remove all break lines and spaces and tabs
            $expected = str_replace($order, $replace, $expected);
            $actual = str_replace($order, $replace, $actual);
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @return xml string  
     */
    public function compareXml() 
    {
        $max = 50;
        $sally = 75;
        $total = $max + $sally;
        $max = $this->convertCurrency($max);
        $sally = $this->convertCurrency($sally);
        $total = $this->convertCurrency($total);
        
        return "<group>
			<title>Proposal/Price Quote</title>
			<value>{$total->subAmount}</value>
			<label>{$total->subAmountFormatted}</label>
			<link>index.php?module=Opportunities&action=index&query=true&searchFormTab=advanced_search&sales_stage=Proposal%2FPrice+Quote</link>
			<subgroups>
				<group>
					<title>will</title>
					<value>NULL</value>
					<label></label>
					<link>index.php?module=Opportunities&action=index&query=true&searchFormTab=advanced_search&sales_stage=Proposal%2FPrice+Quote</link>
				</group>
				<group>
					<title>max</title>
					<value>{$max->subAmount}</value>
					<label>{$max->subAmountFormatted}</label>
					<link>index.php?module=Opportunities&action=index&query=true&searchFormTab=advanced_search&sales_stage=Proposal%2FPrice+Quote&assigned_user_id[]=seed_max_id</link>
				</group>
				<group>
					<title>sarah</title>
					<value>NULL</value>
					<label></label>
					<link>index.php?module=Opportunities&action=index&query=true&searchFormTab=advanced_search&sales_stage=Proposal%2FPrice+Quote</link>
				</group>
				<group>
					<title>sally</title>
					<value>{$sally->subAmount}</value>
					<label>{$sally->subAmountFormatted}</label>
					<link>index.php?module=Opportunities&action=index&query=true&searchFormTab=advanced_search&sales_stage=Proposal%2FPrice+Quote&assigned_user_id[]=seed_sally_id</link>
				</group>
				<group>
					<title>chris</title>
					<value>NULL</value>
					<label></label>
					<link>index.php?module=Opportunities&action=index&query=true&searchFormTab=advanced_search&sales_stage=Proposal%2FPrice+Quote</link>
				</group>
			</subgroups></group>";
    }
    
    public function convertCurrency($value) {
        $sub_amount = $this->sugarChartObject->formatNumber($this->sugarChartObject->convertCurrency($value));
        $sub_amount_formatted = $this->sugarChartObject->currency_symbol . $sub_amount . 'K';
        $sub_amount = $this->sugarChartObject->convertCurrency($value);
        $return = new stdClass();
        $return->subAmount = $sub_amount;
        $return->subAmountFormatted = $sub_amount_formatted;
        return $return;
    }
}