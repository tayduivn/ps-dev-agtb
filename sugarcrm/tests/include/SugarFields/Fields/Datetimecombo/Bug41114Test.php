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
 
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');

class Bug41114Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user;
    
	public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
    }
    
    public function _providerEmailTemplateFormat()
    {
        return array(
            array('2010-10-10 13:00:00','2010/10/10 01:00PM', 'Y/m/d', 'h:iA' ),
            array('2010-10-11 13:00:00','2010/10/11 13:00', 'Y/m/d', 'H:i' ),
            
            array('2011-03-25 01:05:22','25.03.2011 01:05AM', 'd.m.Y', 'h:iA'),
            array('2011-04-21 01:05:22','21.04.2011 01:05', 'd.m.Y', 'H:i'),
            
            array('','', 'Y-m-d', 'h:iA'),
            array('','', 'Y-m-d', 'H:i'),
            
        );   
    }
     /**
     * @dataProvider _providerEmailTemplateFormat
     */
	public function testEmailTemplateFormat($unformattedValue, $expectedValue, $dateFormat, $timeFormat)
	{
	    $GLOBALS['sugar_config']['default_date_format'] = $dateFormat;
		$GLOBALS['sugar_config']['default_time_format'] = $timeFormat;
		$this->user->setPreference('datef', $dateFormat);
		$this->user->setPreference('timef', $timeFormat);
		
        require_once('include/SugarFields/SugarFieldHandler.php');
   		$sfr = SugarFieldHandler::getSugarField('datetimecombo');
    	$formattedValue = $sfr->getEmailTemplateValue($unformattedValue,array(), array('notify_user' => $this->user));
    	
   	 	$this->assertEquals($expectedValue, $formattedValue);
    }
}