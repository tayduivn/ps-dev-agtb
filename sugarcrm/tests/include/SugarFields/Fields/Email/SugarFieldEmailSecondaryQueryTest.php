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
 
require_once('include/SugarFields/Fields/Email/SugarFieldEmail.php');

class SugarFieldEmailSecondaryQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @ticket BR-1307
     */
    public function testAutoPrimary()
    {
        $emailsArray = array(
            array('id' => 'addr_1',
                  'bean_id' => 'bean_2',
                  'email_address' => 'addr_1@bean_2.com',
                  'invalid_email' => true,
                  'opt_out' => false,
            ),
            array('id' => 'addr_2',
                  'bean_id' => 'bean_2',
                  'email_address' => 'addr_2@bean_2.com',
                  'invalid_email' => false,
                  'opt_out' => false,
            ),
            array('id' => 'addr_1',
                  'bean_id' => 'bean_1',
                  'email_address' => 'addr_1@bean_1.com',
                  'invalid_email' => true,
                  'opt_out' => true,
            ),
            array('id' => 'addr_1',
                  'bean_id' => 'bean_3',
                  'email_address' => 'addr_1@bean_3.com',
                  'invalid_email' => false,
                  'opt_out' => false,
            ),
            array('id' => 'addr_2',
                  'bean_id' => 'bean_3',
                  'email_address' => 'addr_2@bean_3.com',
                  'invalid_email' => false,
                  'opt_out' => true,
            ),
            array('id' => 'addr_2',
                  'bean_id' => 'bean_1',
                  'email_address' => 'addr_2@bean_1.com',
                  'invalid_email' => false,
                  'opt_out' => false,
            ),
        );

        $queryMock = $this->getMock('SugarQuery',
                                    array('execute'));
        $queryMock->expects($this->once())
                  ->method('execute')
                  ->will($this->returnValue($emailsArray));
        $emailMock = $this->getMock('EmailAddresses',
                                    array('getEmailsQuery'));
        $emailMock->expects($this->once())
                  ->method('getEmailsQuery')
                  ->will($this->returnValue($queryMock));

        $seed = BeanFactory::newBean('Accounts');
        $seed->emailAddress = $emailMock;
        
        $beans = array();
        $beans['bean_3'] = BeanFactory::newBean('Accounts');
        $beans['bean_3']->id = 'bean_3';
        $beans['bean_1'] = BeanFactory::newBean('Accounts');
        $beans['bean_1']->id = 'bean_1';
        $beans['bean_2'] = BeanFactory::newBean('Accounts');
        $beans['bean_2']->id = 'bean_2';

        $field = SugarFieldHandler::getSugarField('email');
        $field->runSecondaryQuery('email', $seed, $beans);

        foreach ($beans as $bean) {
            foreach ($bean->emailAddress->addresses as $addr) {
                if (strpos($addr['email_address'], 'addr_1@') !== false) {
                    // Address 1 should always be primary
                    $this->assertTrue($addr['primary_address']==true, "Addr_1 is not primary for {$bean->id}");
                } else {
                    $this->assertFalse($addr['primary_address']==true, "Email {$addr['email_address']} is primary for {$bean->id}");                    
                }
            }
        }
    }
}
