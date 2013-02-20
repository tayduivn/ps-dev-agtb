<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'tests/SugarTestDatabaseMock.php';
require_once 'tests/SugarTestReflection.php';
require_once 'include/api/SugarApi.php';
require_once 'data/SugarBeanApiHelper.php';
require_once 'include/api/RestService.php';
require_once 'modules/Users/User.php';

class SugarApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public $db;
    
    static public $monitorList;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        
        self::$monitorList = TrackerManager::getInstance()->getDisabledMonitors();

        self::$db = new SugarTestDatabaseMock();
        self::$db->setUp();
    }

    public static function tearDownAfterClass()
    {
        self::$db->tearDown();
        SugarTestHelper::tearDown();
        ApiHelper::$moduleHelpers = array();
        TrackerManager::getInstance()->setDisabledMonitors(self::$monitorList);
    }

    public function setUp()
    {
        // We can override the module helpers with mocks.
        ApiHelper::$moduleHelpers = array();
    }
    
    public function testFormatBeanCallsTrackView()
    {
        if ( !SugarTestReflection::isSupported() ) {
            $this->markTestSkipped("Need a newer version of PHP, 5.3.2 is the minimum for this test");
        }


        $apiMock = $this->getMock('SugarApi',array('htmlDecodeReturn', 'trackAction'));
        $apiMock->expects($this->any())
                ->method('htmlDecodeReturn');

        $apiMock->expects($this->once())
                ->method('trackAction');
        
        $fakeBean = $this->getMock('SugarBean');
        $fakeBean->id = 'abcd';
        $fakeBean->module_dir = 'fakeBean';

        $apiMock->api = $this->getMock('RestService');

        $helperMock = $this->getMock('SugarBeanApiHelper',array('formatForApi'),array($apiMock->api));
        $helperMock->expects($this->any())
                   ->method('formatForApi')
                   ->will($this->returnValue(
                       array('never gonna'=>
                             array('give you up',
                                   'let you down',
                                   'run around',
                                   'desert you'))));
        ApiHelper::$moduleHelpers['fakeBean'] = $helperMock;
        
        // Call it once when it should track the view
        SugarTestReflection::callProtectedMethod($apiMock,'formatBean',array($apiMock->api,array('viewed'=>true), $fakeBean));

        // And once when it shouldn't
        SugarTestReflection::callProtectedMethod($apiMock,'formatBean',array($apiMock->api,array(), $fakeBean));

        // No asserts, they are handled by the mock's ->expects()
    }

    /*
     * @covers SugarApi::trackAction
     */
    public function testTrackAction()
    {
        $sugarApi = $this->getMockForAbstractClass('SugarApi');
        $sugarApi->api = $this->getMock('RestService');
        $sugarApi->api->user = $this->getMock('User',array('getPrivateTeamID'));
        $sugarApi->api->user
            ->expects($this->any())
            ->method('getPrivateTeamID')
            ->will($this->returnValue('1'));
        $fakeBean = $this->getMock('SugarBean',array('get_summary_text'));
        $fakeBean->id = 'abcd';
        $fakeBean->module_dir = 'fakeBean';
        $fakeBean->expects($this->any())
                 ->method('get_summary_text')
                 ->will($this->returnValue('Rickroll'));
        
        
        $sugarApi->action = 'unittest';
        
        // First test that it returns without running any queries if the monitor is disabled
        $manager = TrackerManager::getInstance();
        $manager->setDisabledMonitors(array('tracker'=>true));

        self::$db->queries = array(
            'saveTracker' => array(
                'match'=>"/.*INSERT INTO tracker.*Rickroll.*/i",
                'rows'=>array(),
                'runCount'=>0,
            ),
            'other' => array(
                'match'=>"/.*/",
                'rows'=>array(),
                'runCount'=>0,
            ),
        );
            
        $sugarApi->trackAction($fakeBean);
        $manager->unsetMonitors();

        $this->assertEquals(0,self::$db->queries['saveTracker']['runCount'],'Tried to insert a tracker record when we shouldn\'t have');
        
        $manager->setDisabledMonitors(array());
        $sugarApi->trackAction($fakeBean);

        $this->assertEquals(1,self::$db->queries['saveTracker']['runCount'],'Didn\'t insert a tracker record when we should have');
        
    }
}
