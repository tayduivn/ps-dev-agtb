<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

require_once 'modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php';

/**
 * Bug #36668
 * Name field is no longer a hyperlink after moving the field from Default to Hidden back to Default 
 * in the Studio subpanel definition for custom module
 * @ticket 36668
 */
class LinkFieldTest extends SubpanelMetaDataParser
{
    /**
     * Field defs without id_name properties were throwing errors. Adding id_name
     * here to allow tests to run around modification to the core code.
     * 
     * @var array
     */
    public $_fielddefs = array(
        'name' => array('module' => 'test', 'id_name' => 'test'),
    );
    
    function __construct()
    {
        return true;
    }
    
    function makeFieldsAsLink($defs)
    {
        return $this->makeRelateFieldsAsLink($defs);
    }
}

class Bug36668Test extends Sugar_PHPUnit_Framework_TestCase
{
    function fieldDefProvider()
    {
        return array(
            array(true, 'relate', '0'),
            array(true, 'name', '1'),
            array(false, 'name', '0'),
        );
    }

    /**
     * @dataProvider fieldDefProvider
     * @group 36668
     */
    public function testMakeRelateFieldsAsLink($flag, $type, $link)
    {
        $defs = array('name' => array('type' => $type, 'link' => $link));
        
        $lt = new LinkFieldTest();
        $newDefs = $lt->makeFieldsAsLink($defs);

        $this->assertTrue(array_key_exists('widget_class', $newDefs['name']) == $flag);
    }
}
