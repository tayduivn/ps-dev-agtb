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
 * Portions created by SugarCRM are Copyright (C) 2004-2013 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'include/vCard.php';

/**
 * Test vCard import with/without all required fields
 * Should not allow import when all required fields are present
 *
 * @author avucinic
 */
class Bug60613Test extends Sugar_PHPUnit_Framework_TestCase
{
    // Since we are creating Beans using vCard Import, must save IDs for cleaning
    private $createdContacts = array();
    private $filename;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->filename = $GLOBALS['sugar_config']['upload_dir'] . 'test.vcf';
    }

    public function tearDown()
    {
        // Clean the Contacts created using vCard Import
        foreach ($this->createdContacts as $contactId)
        {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$contactId}'");
        }
        unlink($this->filename);
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider dataProvider
     * @group bug60613
     */
    public function testImportVCard($contents, $module, $allRequiredPresent)
    {
        file_put_contents($this->filename, $contents);

        $vcard = new vCard();
        $beanId = $vcard->importVCard($this->filename, $module);

        if ($allRequiredPresent)
        {
            $this->createdContacts[] = $beanId;
            $this->assertNotEmpty($beanId);
        }
        else
        {
            $this->assertEmpty($beanId);
        }
    }

    public function dataProvider()
    {
        return array(
            array(
                'BEGIN:VCARD
                N:person;test;
                FN: person lead
                BDAY:
                TEL;FAX:
                TEL;HOME:
                TEL;CELL:
                TEL;WORK:
                EMAIL;INTERNET:
                ADR;WORK:;;;;;;
                TITLE:
                END:VCARD', // vCard with all required fields
                'Contacts',
                true),
            array(
                'BEGIN:VCARD
                BDAY:
                TEL;FAX:
                TEL;HOME:
                TEL;CELL:
                TEL;WORK:
                EMAIL;INTERNET:
                ADR;WORK:;;;;;;
                TITLE:
                END:VCARD', // vCard without last_name
                'Contacts',
                false),
            array(
                '', // Empty vCard
                'Contacts',
                false),
        );
    }
}
