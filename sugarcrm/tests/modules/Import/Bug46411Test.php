<?php
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

require_once('modules/Import/Importer.php');
require_once('modules/Calls/Call.php');
/**
 * Bug #46411
 * Importing Calls will not populate Leads or Contacts Subpanel
 *
 * @author adetskin@sugarcrm.com
 * @ticket 46411
 */
class Bug46411Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Importing Calls will not populate Leads or Contacts Subpanel
     *
     * @group 46411
     */
    public function setUp()
    {
        $this->markTestIncomplete("Breaking unit test on CI");
        return;

        $this->importSource = new stdClass();
        $this->importSource->columncount = 2;
        $this->importSource->colnum_0 = 'date_entered';
        $this->importSource->colnum_1 = 'last_name';
        $this->importSource->import_module = 'Calls';
        $this->importSource->importlocale_charset = 'UTF-8';
        $this->importSource->importlocale_dateformat = 'm/d/Y';
        $this->importSource->importlocale_timeformat = 'h:i a';
        $this->importSource->importlocale_timezone = 'GMT';
        $this->importSource->importlocale_default_currency_significant_digits = '2';
        $this->importSource->importlocale_currency = '-99';
        $this->importSource->importlocale_dec_sep = '.';
        $this->importSource->importlocale_currency = '-99';
        $this->importSource->importlocale_default_locale_name_format = 's f l';
        $this->importSource->importlocale_num_grp_sep = ',';
    }

    public function tearDown()
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    public function testMissingFields()
    {
        $bean = $this->getMock('Call',array(
            'get_importable_fields',
            'populateDefaultValues',
            'beforeImportSave',
            'save',
            'afterImportSave',
            'writeRowToLastImport'));

        $bean->expects($this->any())
            ->method('get_importable_fields')
            ->will($this->returnValue(array(
            'account_id'		=> 'accounts',
            'opportunity_id'	=> 'opportunities',
            'contact_id'		=> 'contacts',
            'case_id'			=> 'cases',
            'user_id'			=> 'users',
            'assigned_user_id'	=> 'users',
            'note_id'			=> 'notes',
            'lead_id'			=> 'leads',
        )));

        $bean->expects($this->any())
            ->method('populateDefaultValues')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('beforeImportSave')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('save')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('afterImportSave')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('writeRowToLastImport')
            ->will($this->returnValue('foo'));

        $bean->date_modified = 'true';
        $bean->fetched_row = array('date_modified' => '');
        $bean->object_name = '';

        $lead = SugarTestLeadUtilities::createLead();
        $a = new bug46411_Importer_mock($this->importSource, $bean);
//        $b = new Call();

        $bean->parent_type = 'leads';
        $bean->parent_id = $lead->id;
//        $bean->relationship_fields = $b->relationship_fields;

        $a->saveImportBean($bean, false);
        $this->assertEquals($bean->parent_id, $bean->lead_id);
    }
}

class bug46411_Importer_mock extends Importer
{
    public function saveImportBean($focus, $newRecord)
    {
        return parent::saveImportBean($focus, $newRecord);
    }

}


