<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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

require_once ('modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php');

/**
 * Bug #44428
 * Studio | Tab Order causes layout errors
 * @ticket 44428
 */
class Bug44428Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['sugar_config']['default_language']);
    }

    public function tearDown()
    {
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    public function providerField()
    {
        return array(
            array('quote_name', '1'),
            array('opportunity_name', ''),
            array(array('name' => 'quote_num', 
                        'type' => 'readonly'), '3'),
            ); 
    }
    /**
     * @dataProvider providerField 
     * @group 44428
     */
    public function testGetNewRowItem($name, $tabindex)
    {
        $source = $name;
        $fielddef['tabindex'] = $tabindex;
        
        $glmdp = new GridLayoutMetaDataParser('editview', 'Quotes');
        $result = $glmdp->getNewRowItem($source, $fielddef);
        
        if (is_array($name))
        {
            $this->assertEquals($result['name'], $name['name']);
        }
        else
        {
            if (empty($tabindex))
            {
                $this->assertEquals($result, $name);
            }
            else
            {
                $this->assertEquals($result['name'], $name);
            }
        }
    }
}