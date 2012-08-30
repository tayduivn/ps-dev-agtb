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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/MetaDataManager/MetaDataConverter.php';

class MetaDataConverterTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testConvertWirelessListToLegacy() {
        $file = 'modules/Bugs/clients/mobile/views/list/list.php';
        require $file;
        
        $this->assertInternalType('array', $viewdefs['Bugs']['mobile']['view']['list'], 'Expected view def structure not found');
        
        $converted = MetaDataConverter::toLegacy('list', $viewdefs['Bugs']['mobile']['view']['list']);
        $this->assertArrayHasKey('BUG_NUMBER', $converted, 'BUG_NUMBER missing from the conversion');
        $this->assertArrayHasKey('NAME', $converted, 'NAME missing from the conversion');
    }
    
    public function testConvertWirelessDetailToLegacy() {
        $file = 'modules/Bugs/clients/mobile/views/detail/detail.php';
        require $file;
        
        $this->assertInternalType('array', $viewdefs['Bugs']['mobile']['view']['detail'], 'Expected view def structure not found');
        
        $converted = MetaDataConverter::toLegacy('detail', $viewdefs['Bugs']['mobile']['view']['detail']);
        $this->assertNotEmpty($converted['panels'][0][0], 'First string field name is missing');
        $this->assertEquals('bug_number', $converted['panels'][0][0], 'First field name is not as expected');
    }
    
    public function testNoConversionForNonConvertableViewType() {
        $file = 'modules/Bugs/clients/mobile/views/search/search.php';
        require $file;
        
        $this->assertArrayHasKey('layout', $searchdefs['Bugs'], 'No layout found where layout was expected');
        
        $converted = MetaDataConverter::toLegacy('search', $searchdefs);
        $this->assertEquals($converted, $searchdefs, 'Viewdefs converted unexpectedly');
    }
}

