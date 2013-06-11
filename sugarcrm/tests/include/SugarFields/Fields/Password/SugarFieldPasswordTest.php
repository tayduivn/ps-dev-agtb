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

require_once 'include/SugarFields/Fields/Password/SugarFieldPassword.php';
require_once 'modules/Import/ImportFieldSanitize.php';

class SugarFieldPasswordTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $fieldObj;
    protected $contactBean;
    protected $currentPassword;

    protected function setUp()
    {
        $this->fieldObj = new SugarFieldPassword('Password');
    }

    protected function tearDown()
    {
        unset($this->fieldObj);
    }

    /**
     * @ticket 40304
     */
    public function testImportSanitize()
    {
        $settings = new ImportFieldSanitize();

        $this->assertEquals(
            md5('test value'),
            $this->fieldObj->importSanitize('test value',array(),null,$settings)
            );
    }

    /**
     * Test formatting the apiFormatField method of a Password field
     */
    public function testApiFormatField()
    {
        $data = array(
            'id' => 'awesome',
            'user_hash' => 'this-is-my-password',
        );

        $bean = BeanFactory::getBean('Users');
        $args = array();
        $fieldName = 'user_hash';
        $properties = array();
        // no bean password set, so it returns empty string
        $this->fieldObj->apiFormatField($data, $bean, $args, $fieldName, $properties);
        $this->assertEquals('', $data['user_hash']);
        $this->assertEquals('awesome', $data['id']);

        $bean->user_hash = 'this-is-my-password';
        // bean password set so it returns value_setvalue_setvalue_set
        $this->fieldObj->apiFormatField($data, $bean, $args, $fieldName, $properties);
        $this->assertEquals(true, $data['user_hash']);
        $this->assertEquals('awesome', $data['id']);
    }

    /**
     * Test the apiSave method of a Password field
     */
    public function testApiSave()
    {
        $contactBean = BeanFactory::getBean('Contacts');
        $contactBean->portal_password = User::getPasswordHash('awesome');
        $currentPassword = $contactBean->portal_password;

        // dataProvider is not working when you need to check class vars
        // test password not change
        $this->fieldObj->apiSave($contactBean, array('portal_password' => true), 'portal_password', array());
        $this->assertEquals($currentPassword, $contactBean->portal_password, "Password should not have changed");

        // test password being unset
        $this->fieldObj->apiSave($contactBean, array('portal_password' => ''), 'portal_password', array());
        $this->assertEquals(null, $contactBean->portal_password, "Password should be null");

        // test changing password
        $this->fieldObj->apiSave($contactBean, array('portal_password' => '1234'), 'portal_password', array());
        $this->assertTrue(User::checkPassword('1234', $contactBean->portal_password), "The password didn't change");
    }
}
