<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

/**
 * Bug46098Test
 *
 * This class contains the unit test to check that the repairSearchFields method will create a SearchFields.php file
 * to correctly handle range searching for date fields.
 *
 */

require_once('modules/UpgradeWizard/uw_utils.php');

class Bug46028Test extends Sugar_PHPUnit_Framework_TestCase
{

var $customOpportunitiesSearchFields;
var $opportunitiesSearchFields;

public function setUp()
{
    $beanList = array();
    $beanFiles = array();
    require('include/modules.php');
    $GLOBALS['beanList'] = $beanList;
    $GLOBALS['beanFiles'] = $beanFiles;
    if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'))
    {
        $this->customOpportunitiesSearchFields = file_get_contents('custom/modules/Opportunities/metadata/SearchFields.php');
        unlink('custom/modules/Opportunities/metadata/SearchFields.php');
    }

    if(file_exists('modules/Opportunities/metadata/SearchFields.php'))
    {
        $this->opportunitiesSearchFields = file_get_contents('modules/Opportunities/metadata/SearchFields.php');
        unlink('modules/Opportunities/metadata/SearchFields.php');
    }

$searchFieldContents = <<<EOQ
<?php
\$searchFields['Opportunities'] =
array (
    'name' => array( 'query_type'=>'default'),
    'account_name'=> array('query_type'=>'default','db_field'=>array('accounts.name')),
    'amount'=> array('query_type'=>'default'),
    'next_step'=> array('query_type'=>'default'),
    'probability'=> array('query_type'=>'default'),
    'lead_source'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'lead_source_dom', 'template_var' => 'LEAD_SOURCE_OPTIONS'),
    'opportunity_type'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'opportunity_type_dom', 'template_var' => 'TYPE_OPTIONS'),
    'sales_stage'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'sales_stage_dom', 'template_var' => 'SALES_STAGE_OPTIONS', 'options_add_blank' => true),
    'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
    'assigned_user_id'=> array('query_type'=>'default'),
    'favorites_only' => array(
    'query_type'=>'format',
                'operator' => 'subquery',
                'subquery' => 'SELECT sugarfavorites.record_id FROM sugarfavorites
                                    WHERE sugarfavorites.deleted=0
                                        and sugarfavorites.module = \'Opportunities\'
                                        and sugarfavorites.assigned_user_id = \'{0}\'',
                'db_field'=>array('id')),
);
?>
EOQ;

    file_put_contents('modules/Opportunities/metadata/SearchFields.php', $searchFieldContents);
}

public function tearDow()
{
    if(!empty($this->customOpportunitiesSearchFields))
    {
        file_put_contents('custom/modules/Opportunities/metadata/SearchFields.php', $this->customOpportunitiesSearchFields);
    } else if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php')) {
        unlink('custom/modules/Opportunities/metadata/SearchFields.php');
    }

    if(!empty($this->opportunitiesSearchFields))
    {
        file_put_contents('modules/Opportunities/metadata/SearchFields.php', $this->opportunitiesSearchFields);
    }
}

public function testRepairSearchFields()
{
    repairSearchFields('modules/Opportunities/metadata/SearchFields.php');
    $this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
    require('custom/modules/Opportunities/metadata/SearchFields.php');
    $this->assertArrayHasKey('range_date_entered', $searchFields['Opportunities']);
    $this->assertArrayHasKey('start_range_date_entered', $searchFields['Opportunities']);
    $this->assertArrayHasKey('end_range_date_entered', $searchFields['Opportunities']);
    $this->assertArrayHasKey('range_date_modified', $searchFields['Opportunities']);
    $this->assertArrayHasKey('start_range_date_modified', $searchFields['Opportunities']);
    $this->assertArrayHasKey('end_range_date_modified', $searchFields['Opportunities']);
}

}
?>