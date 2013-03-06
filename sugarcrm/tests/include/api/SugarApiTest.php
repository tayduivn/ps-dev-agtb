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
    protected $mock;

    static public $monitorList;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$monitorList = TrackerManager::getInstance()->getDisabledMonitors();

        self::$db = new SugarTestDatabaseMock();
        self::$db->setUp();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        self::$db->tearDown();
        SugarTestHelper::tearDown();
        ApiHelper::$moduleHelpers = array();
        TrackerManager::getInstance()->setDisabledMonitors(self::$monitorList);
    }

    public function setUp() {
        $this->mock = new SugarApiMock();
        $this->contact = SugarTestContactUtilities::createContact();
        // We can override the module helpers with mocks.
        ApiHelper::$moduleHelpers = array();
    }

    public function tearDown() {
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testLoadBeanById_BeanExists_Success() {
        $this->mock = new SugarApiMock();

        $args = array(
            'module' => 'Contacts',
            'record' => $this->contact->id
        );

       $api = new SugarApiTestServiceMock();
       $bean=$this->mock->callLoadBean($api, $args);

       $this->assertTrue($bean instanceof Contact);
       $this->assertEquals($this->contact->id, $bean->id, "Unexpected Contact Loaded");
    }

    public function testLoadBeanById_BeanNotExists_NotFound() {
        $this->mock = new SugarApiMock();

        $args = array(
            'module' => 'Contacts',
            'record' => '12345'
        );

        $api = new SugarApiTestServiceMock();
        $this->setExpectedException('SugarApiExceptionNotFound');
        $bean=$this->mock->callLoadBean($api, $args);
    }

    public function testLoadBean_CreateTempBean_Success() {
        $this->mock = new SugarApiMock();

        $args = array( /* Note: No "record" element */
            'module' => 'Contacts',
        );

        $api = new SugarApiTestServiceMock();
        $bean=$this->mock->callLoadBean($api, $args);

        $this->assertTrue($bean instanceof Contact);
        $this->assertEquals( '', $bean->first_name . $bean->first_name,  "Unexpected Contact Loaded");
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
        $monitorMock = $this->getMockBuilder('Monitor')
            ->disableOriginalConstructor()
            ->getMock(array('setValue'));
        $monitorMock
            ->expects($this->any())
            ->method('setValue');

        $managerMock = $this->getMockBuilder('TrackerManager')
            ->disableOriginalConstructor()
            ->getMock(array('getMonitor','saveMonitor'));
        $managerMock
            ->expects($this->once())
            ->method('saveMonitor');
        
        $sugarApi = $this->getMock('SugarApi',array('getTrackerManager'));
        $sugarApi
            ->expects($this->any())
            ->method('getTrackerManager')
            ->will($this->returnValue($managerMock));
        
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
        
        // Emulate the tracker being disabled, then enabled
        $managerMock
            ->expects($this->any())
            ->method('getMonitor')
            ->will($this->onConsecutiveCalls(null,$monitorMock,$monitorMock,$monitorMock,$monitorMock));
        
        $sugarApi->trackAction($fakeBean);

        // This one should actually save
        $sugarApi->trackAction($fakeBean);

        // Try it again, but this time with a new bean with id
        $fakeBean->new_with_id = true;
        $sugarApi->trackAction($fakeBean);

        // And one last time but this time with an empty bean id
        unset($fakeBean->new_with_id);
        unset($fakeBean->id);
        $sugarApi->trackAction($fakeBean);

        // No asserts, handled through the saveMonitor ->once() expectation above
    }

    /**
     * @dataProvider lotsOData
     */
    public function testHtmlEntityDecode($array, $expected, $message) {
        $this->mock->htmlEntityDecodeStuff($array);
        $this->assertSame($array, $expected, $message);
    }

    public function lotsOData()
    {
        return array(
                array(array("bool" => true), array("bool"=>true), "True came out wrong"),
                array(array("bool" => false), array("bool"=>false), "False came out wrong"),
                array(array("string" => 'Test'), array("string"=>'Test'), "String came out wrong"),
                array(array("html" => htmlentities("I'll \"walk\" the <b>dog</b> now")), array("html"=>"I'll \"walk\" the <b>dog</b> now"), "HTML came out wrong"),
            );
    }
}


// need to make sure ServiceBase is included when extending it to avoid a fatal error
require_once("include/api/ServiceBase.php");

class SugarApiMock extends SugarApi
{
    public function htmlEntityDecodeStuff(&$data)
    {
        return parent::htmlDecodeReturn($data);
    }

    public function callLoadBean(ServiceBase $api, $args)
    {
        return parent::loadBean($api, $args);
    }
}

class SugarApiTestServiceMock extends ServiceBase
{
    public function execute() {}

    protected function handleException(Exception $exception) {}
}
