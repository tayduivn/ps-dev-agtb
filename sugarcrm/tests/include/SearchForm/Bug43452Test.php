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

require_once "modules/Leads/Lead.php";
require_once "include/Popups/PopupSmarty.php";

class Bug43452Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }
    
    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 43452
     */
    public function testGenerateSearchWhereWithUnsetBool()
    {
        // Looking for a NON Converted Lead named "Fabio".
        // Without changes, PopupSmarty return a bad query, with AND and OR at the same level.
        // With this fix we get parenthesis:
        //     1) From SearchForm2->generateSearchWhere, in case of 'bool' (they surround "converted = '0' or converted IS NULL")
        //     2) From PopupSmarty->_get_where_clause, when items of where's array are imploded.

        $tGoodWhere = "( leads.first_name like 'Fabio%' and ( leads.converted = '0' OR leads.converted IS NULL ) )";

        $_searchFields['Leads'] = array ('first_name'=> array('value' => 'Fabio', 'query_type'=>'default'),
                                         'converted'=> array('type'=> 'bool', 'value' => '0', 'query_type'=>'default'),
                                        );
        // provides $searchdefs['Leads']
        require "modules/Leads/metadata/searchdefs.php";
        
        $bean = BeanFactory::getBean('Leads');
        $popup = new PopupSmarty($bean, "Leads");
        $popup->searchForm->searchdefs =  $searchdefs['Leads'];
        $popup->searchForm->searchFields = $_searchFields['Leads'];
        $tWhere = $popup->_get_where_clause();

        $this->assertEquals($tGoodWhere, $tWhere);
    }
}
