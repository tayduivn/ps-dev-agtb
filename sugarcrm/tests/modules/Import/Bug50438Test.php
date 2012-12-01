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


/*
 * This tests whether a relationship with parent bean is saved during import.  We simulate a call being imported with
 * parent_id and parent_type columns filled out, which should save the relationship even during import
 * @ticket 50438
 */

require_once('modules/Import/Importer.php');
require_once('modules/Import/sources/ImportFile.php');

class Bug50438Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $contact;
    var $fileArr;
    var $call_id;
    public function setUp()
    {
        global $currentModule ;
        $this->call_id = create_guid();
		$mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //create a contact
        $this->contact = new Contact();
        $this->contact->first_name = 'Joe UT ';
        $this->contact->last_name = 'Smith UT 50438';
        $this->contact->disable_custom_fields = true;
        $this->contact->save();

        //create array to output as import file using the new contact as the related parent
        $this->fileArr = array(
            0=> "\"{$this->call_id}\",\"Call for Unit Test 50438\",\"Planned\", \"{$this->contact->module_dir}\",\"{$this->contact->id}\""
        );
    }

    public function tearDown()
    {

        $GLOBALS['db']->query("DELETE FROM calls WHERE id='{$this->call_id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id='{$this->contact->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->call_id);
        unset($this->contact);
        unset($this->fileArr);
        unset( $GLOBALS['current_user']);
        unset( $GLOBALS['mod_strings']);
    }



    public function testParentsAreRelatedDuringImport()
    {

        $file = 'upload://test50438.csv';
        $ret = file_put_contents($file, $this->fileArr);
        $this->assertGreaterThan(0, $ret, 'Failed to write to '.$file .' for content '.var_export($this->fileArr,true));

        $importSource = new ImportFile($file, ',', '"');

        $bean = BeanFactory::getBean('Calls');
        $bean->date_start = TimeDate::getInstance()->getNow()->asDb();

        $_REQUEST['columncount'] = 5;
        $_REQUEST['colnum_0'] = 'id';
        $_REQUEST['colnum_1'] = 'subject';
        $_REQUEST['colnum_2'] = 'status';
        $_REQUEST['colnum_3'] = 'parent_type';
        $_REQUEST['colnum_4'] = 'parent_id';
        $_REQUEST['import_module'] = 'Contacts';
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';
        $_REQUEST['importlocale_dateformat'] = 'm/d/y';
        $_REQUEST['importlocale_timeformat'] = 'h:i:s';

        $importer = new Importer($importSource, $bean);
        $importer->import();

        //fetch the bean using the passed in id and get related contacts
        require_once('modules/Calls/Call.php');
        $call = new Call();
        $call->retrieve($this->call_id);
        $call->load_relationship('contacts');
        $related_contacts = $call->contacts->get();

        //test that the contact id is in the array of related contacts.
        $this->assertContains($this->contact->id, $related_contacts,' Contact was not related during simulated import despite being set in related parent id');
        unset($call);

        /*
        if (is_file($file)) {
            unlink($file);
        }
        */
    }

}