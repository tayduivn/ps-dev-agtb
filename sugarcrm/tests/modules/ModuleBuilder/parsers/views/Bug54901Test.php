<?php
//FILE SUGARCRM flav=ent ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/views/SidecarListLayoutMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php';

/**
 * Accessor class, in the event the parsers public properties go protected, which
 * they are slated to do.
 */
class Bug54901TestListParser extends SidecarListLayoutMetaDataParser {
    public function changeFieldType($field, $type) {
        $this->_fielddefs[$field]['type'] = $type;
    }
}

class Bug54901TestGridParser extends SidecarGridLayoutMetaDataParser {
    public function changeFieldType($field, $type) {
        $this->_fielddefs[$field]['type'] = $type;
    }

    public function isAvailableFieldName($name, $fields) {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] == $name) {
                return true;
            }
        }

        return false;
    }
}

class Bug54901Test extends Sugar_PHPUnit_Framework_TestCase {
    public function setUp() {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    public function testPortalListLayoutDoesNotIncludeInvalidFields() {
        // Build the parser
        $list = new Bug54901TestListParser(MB_PORTALLISTVIEW, 'Cases', '', MB_PORTAL);
        
        // Massage the field defs
        $list->changeFieldType('resolution', 'iframe');
        $list->changeFieldType('system_id', 'encrypt');
        $list->changeFieldType('portal_viewable', 'relate');
        
        // Get our fields
        $fields = $list->getAvailableFields();
        
        // Run the assertions
        $this->assertArrayNotHasKey('resolution', $fields, 'The resolution field was not excluded');
        $this->assertArrayNotHasKey('system_id', $fields, 'The system_id field was not excluded');
        $this->assertArrayHasKey('portal_viewable', $fields, 'portal_viewable was excluded but a relate type should not be excluded');
        $this->assertArrayHasKey('description', $fields, 'Description is showing as not available');
    }
    
    public function testPortalRecordLayoutDoesNotIncludeInvalidFields() {
        // Build the parser
        $grid = new Bug54901TestGridParser(MB_PORTALRECORDVIEW, 'Cases', '', MB_PORTAL);
        
        // Massage the field defs
        $grid->changeFieldType('resolution', 'parent');
        $grid->changeFieldType('system_id', 'encrypt');
        $grid->changeFieldType('work_log', 'relate');
        
        // Get our fields
        $fields = $grid->getAvailableFields();

        // Run the assertions
        $available = $grid->isAvailableFieldName('resolution', $fields);
        $this->assertFalse($available, 'The resolution field was not excluded');

        $available = $grid->isAvailableFieldName('system_id', $fields);
        $this->assertFalse($available, 'The system_id field was not excluded');

        $available = $grid->isAvailableFieldName('work_log', $fields);
        $this->assertTrue($available, 'Work Log is showing as not available');
    }
}