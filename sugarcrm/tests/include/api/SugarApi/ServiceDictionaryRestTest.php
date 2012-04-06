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

require_once 'include/api/SugarApi/ServiceDictionaryRest.php';

class ServiceDictionaryRestTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testRegisterEndpoints() {
        $sd = new ServiceDictionaryRest();
        
        $sd->preRegisterEndpoints();
        $blank = $sd->getRegisteredEndpoints();
        
        $this->assertEquals(0,count($blank));
        
        $sd->preRegisterEndpoints();
        $fakeEndpoints = array(
            array('reqType'=>'GET',
                  'path'=>array('one','two','three'),
                  'pathVars'=>array('','',''),
                  'method'=>'unittest1',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
            ),
            array('reqType'=>'GET',
                  'path'=>array('one','two'),
                  'pathVars'=>array('',''),
                  'method'=>'unittest2',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
            ),
            array('reqType'=>'GET',
                  'path'=>array('one','two','three'),
                  'pathVars'=>array('','',''),
                  'method'=>'unittest3',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
                  'extraScore'=>25.5,
            ),
            array('reqType'=>'GET',
                  'path'=>array('<module>','?','three'),
                  'pathVars'=>array('','',''),
                  'method'=>'unittest4',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
            ),
        );
        $sd->registerEndpoints(array($fakeEndpoints[0]),'fake/unittest1.php','unittest1',0);
        
        $oneTest = $sd->getRegisteredEndpoints();

        $this->assertTrue(isset($oneTest['3']['GET']['one']['two']['three'][0]['method']));

        $sd->preRegisterEndpoints();
        $sd->registerEndpoints($fakeEndpoints,'fake/unittest1.php','unittest1',0);
        
        $allTest = $sd->getRegisteredEndpoints();

        $this->assertTrue(isset($allTest['3']['GET']['one']['two']['three'][0]['method']));
        $this->assertTrue(isset($allTest['2']['GET']['one']['two'][0]['method']));
    }
}