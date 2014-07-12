<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$monitorList = TrackerManager::getInstance()->getDisabledMonitors();

        self::$db = SugarTestHelper::setUp('mock_db');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        ApiHelper::$moduleHelpers = array();
        TrackerManager::getInstance()->setDisabledMonitors(self::$monitorList);
        parent::tearDownAfterClass();
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
        $this->setExpectedException('SugarApiExceptionMissingParameter');
        $bean=$this->mock->callLoadBean($api, $args);
    }

    public function testFormatBeanCallsTrackView()
    {
        $this->markTestIncomplete("SugarApi needs a user to pass along to other objects, and user is not getting passed along. Sending to FRM for fix.");
        
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
    public function testHtmlEntityDecode($array, $expected, $message)
    {
        $this->mock->htmlEntityDecodeStuff($array);
        $this->assertSame($array, $expected, $message);
    }

    public function lotsOData()
    {
        return array(
            array(array("bool" => true), array("bool" => true), "True came out wrong"),
            array(array("bool" => false), array("bool" => false), "False came out wrong"),
            array(array("string" => 'Test'), array("string" => 'Test'), "String came out wrong"),
            array(
                array("html" => htmlentities("I'll \"walk\" the <b>dog</b> now")),
                array("html" => "I'll \"walk\" the <b>dog</b> now"),
                "HTML came out wrong"
            ),
            array(
                array("html" => array("nested_result" => array("data" => "def &lt; abc &gt; xyz"))),
                array("html" => array("nested_result" => array("data" => "def < abc > xyz"))),
                "HTML came out wrong"
            ),
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
