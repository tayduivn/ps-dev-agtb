<?php
//FILE SUGARCRM flav=ent ONLY
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

/**
 * Bug54507Test.php
 *
 * Tests available fields for portal list, edit and detail to make sure that
 * duplicate labels are not included for the Bugs module.
 */

require_once('modules/ModuleBuilder/parsers/ParserFactory.php');

class Bug54507Test extends Sugar_PHPUnit_Framework_TestCase {
    protected $editModule = 'Bugs';

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testBugsPortalEditAvailableFields()
    {
        $view = 'portaleditview';
        $parser = ParserFactory::getParser($view, $this->editModule, null, null, MB_PORTAL);
        $fielddefs = $parser->getFieldDefs();

        $test = AbstractMetaDataParser::validField($fielddefs['fixed_in_release'], $view);
        $this->assertTrue($test, 'fixed_in_release should be a valid available field in Bugs Portal Edit View');

        $test = AbstractMetaDataParser::validField($fielddefs['fixed_in_release_name'], $view);
        $this->assertFalse($test, 'fixed_in_release_name should not be a valid available field in Bugs Portal Edit View');

        $test = AbstractMetaDataParser::validField($fielddefs['fixed_in_release_link'], $view);
        $this->assertFalse($test, 'fixed_in_release_link should not be a valid available field in Bugs Portal Edit View');

        $test = AbstractMetaDataParser::validField($fielddefs['found_in_release'], $view);
        $this->assertTrue($test, 'found_in_release should be a valid available field in Bugs Portal Edit View');

        $test = AbstractMetaDataParser::validField($fielddefs['release_name'], $view);
        $this->assertFalse($test, 'release_name should not be a valid available field in Bugs Portal Edit View');

        $test = AbstractMetaDataParser::validField($fielddefs['release_link'], $view);
        $this->assertFalse($test, 'release_link should not be a valid available field in Bugs Portal Edit View');
    }

    public function testBugsPortalDetailAvailableFields()
    {
        $view = 'portaldetailview';
        $parser = ParserFactory::getParser($view, $this->editModule, null, null, MB_PORTAL);
        $fielddefs = $parser->getFieldDefs();

        $test = AbstractMetaDataParser::validField($fielddefs['fixed_in_release'], $view);
        $this->assertTrue($test, 'fixed_in_release should be a valid available field in Bugs Portal Detail View');

        $test = AbstractMetaDataParser::validField($fielddefs['fixed_in_release_name'], $view);
        $this->assertFalse($test, 'fixed_in_release_name should not be a valid available field in Bugs Portal Detail View');

        $test = AbstractMetaDataParser::validField($fielddefs['fixed_in_release_link'], $view);
        $this->assertFalse($test, 'fixed_in_release_link should not be a valid available field in Bugs Portal Detail View');

        $test = AbstractMetaDataParser::validField($fielddefs['found_in_release'], $view);
        $this->assertTrue($test, 'found_in_release should be a valid available field in Bugs Portal Detail View');

        $test = AbstractMetaDataParser::validField($fielddefs['release_name'], $view);
        $this->assertFalse($test, 'release_name should not be a valid available field in Bugs Portal Detail View');

        $test = AbstractMetaDataParser::validField($fielddefs['release_link'], $view);
        $this->assertFalse($test, 'release_link should not be a valid available field in Bugs Portal Detail View');
    }

    public function testBugsPortalListAvailableFields()
    {
        $parser = ParserFactory::getParser(MB_PORTALLISTVIEW, $this->editModule, null, null, MB_PORTAL);
        $fielddefs = $parser->getFieldDefs();

        $test = $parser->isValidField('fixed_in_release', $fielddefs['fixed_in_release']);
        $this->assertFalse($test, 'fixed_in_release should not be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('date_modified', $fielddefs['date_modified']);
        $this->assertTrue($test, 'date_modified should be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('fixed_in_release_link', $fielddefs['fixed_in_release_link']);
        $this->assertFalse($test, 'fixed_in_release_link should not be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('found_in_release', $fielddefs['found_in_release']);
        $this->assertFalse($test, 'found_in_release should not be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('system_id', $fielddefs['system_id']);
        $this->assertTrue($test, 'system_id should be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('release_link', $fielddefs['release_link']);
        $this->assertFalse($test, 'release_link should not be a valid available field in Bugs Portal List View');
    }
}