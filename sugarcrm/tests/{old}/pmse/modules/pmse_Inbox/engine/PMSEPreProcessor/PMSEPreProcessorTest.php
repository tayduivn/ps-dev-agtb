<?php
//FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

class PMSEPreProcessorTest extends TestCase
{
    protected $loggerMock;

    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMockBuilder("PSMELogger")
                ->disableOriginalConstructor()
                ->setMethods(array('info', 'debug'))
                ->getMock();
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetFlowDataListDirect()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = array('idFlow' => '282', 'id' => '676', 'cas_id'=> '191');
        $request->setType('direct');
        $request->setArguments($arguments);

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlowById'))
                ->getMock();

        $preProcessorMock->expects($this->once())
                ->method('getFlowById')
                ->will($this->returnValue(array(true)));

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals(array(true), $result);
    }

    public function testGetFlowDataListQueue()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = array('idFlow' => '282', 'id' => '676', 'cas_id'=> '191');
        $request->setArguments($arguments);
        $request->setType('queue');

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlowById'))
                ->getMock();

        $preProcessorMock->expects($this->once())
                ->method('getFlowById')
                ->will($this->returnValue(array(true)));

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals(array(true), $result);
    }

    public function testGetFlowDataListEngine()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = array('idFlow' => '282', 'id' => '676', 'cas_id'=> '191');
        $request->setArguments($arguments);
        $request->setType('engine');

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlowsByCasId'))
                ->getMock();

        $preProcessorMock->expects($this->once())
                ->method('getFlowsByCasId')
                ->will($this->returnValue(array(true)));

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals(array(true), $result);
    }

    public function testGetFlowDataListInvalid()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = array('idFlow' => '282', 'id' => '676', 'cas_id'=> '191');
        $request->setArguments($arguments);
        $request->setType('invalid_type');

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals(array(), $result);
    }

    public function testGetAllEvents()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods([
                'getModuleName',
            ])
            ->getMock();

        $beanMock->expects($this->once())
            ->method('getModuleName')
            ->will($this->returnValue('Accounts'));

        $beanMock->id = 'T1234';

        $beanMock->db = DBManagerFactory::getInstance();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods([
                'getValidLinks',
                'buildLinkedObjectIdList',
            ])
            ->getMock();

        $preProcessorMock->expects($this->once())
            ->method('getValidLinks')
            ->will($this->returnValue([]));

        $preProcessorMock->expects($this->once())
            ->method('buildLinkedObjectIdList')
            ->will($this->returnValue(['T1234']));

        $preProcessorMock->getAllEvents($beanMock);
    }

    public function testProcessRequestValidRequest()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->setMethods([
                'getBean',
            ])
            ->getMock();

        $requestMock->expects($this->once())
                ->method('getBean')
                ->will($this->returnValue($beanMock));

        $requestMock->setBean($beanMock);

        $resultRequest = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $resultRequest->validate();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
                ->disableOriginalConstructor()
                ->setMethods([
                    'getFlowDataList',
                    'processFlowData',
                    'processBean',
                ])
                ->getMock();

        $preProcessorMock->expects($this->any())
                ->method('processBean')
                ->will($this->returnValue($beanMock));

        $preProcessorMock->expects($this->once())
                ->method('getFlowDataList')
                ->will($this->returnValue([
                    [
                        'bpmn_type' => 'bpmEvent',
                        'bpmn_id'=>'event_0',
                        'cas_id' => 1,
                        // Added so that subject data setter doesn't die
                        'prj_id' => 'foo',
                        'pro_id' => 'bar',
                    ],
                ]));

        $validatorMock = $this->getMockBuilder('PMSEValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('validateRequest'))
                ->getMock();

        $validatorMock->expects($this->once())
                ->method('validateRequest')
                ->will($this->returnValue($resultRequest));

        $executerMock = $this->getMockBuilder('PMSEExecuter')
                ->disableOriginalConstructor()
                ->setMethods(array('runEngine'))
                ->getMock();

        $executerMock->expects($this->once())
                ->method('runEngine');

        $preProcessorMock->setExecuter($executerMock);
        $preProcessorMock->setLogger($this->loggerMock);
        $preProcessorMock->setValidator($validatorMock);

        $preProcessorMock->processRequest($requestMock);
    }

    public function testProcessRequestInvalidRequest()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->setMethods([
                'getBean',
            ])
            ->getMock();

        $requestMock->expects($this->once())
                ->method('getBean')
                ->will($this->returnValue($beanMock));

        $requestMock->setBean($beanMock);

        $resultRequest = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $resultRequest->invalidate();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
                ->disableOriginalConstructor()
                ->setMethods([
                    'getFlowDataList',
                    'processFlowData',
                    'processBean',
                ])
                ->getMock();

        $preProcessorMock->expects($this->any())
                ->method('processBean')
                ->will($this->returnValue($beanMock));

        $preProcessorMock->expects($this->once())
                ->method('getFlowDataList')
                ->will($this->returnValue([
                    [
                        'bpmn_type' => 'bpmEvent',
                        'bpmn_id'=>'event_0',
                        'cas_id' => 1,
                        // Added so that subject data setter doesn't die
                        'prj_id' => 'foo',
                        'pro_id' => 'bar',
                    ],
                ]));

        $validatorMock = $this->getMockBuilder('PMSEValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('validateRequest'))
                ->getMock();

        $validatorMock->expects($this->once())
                ->method('validateRequest')
                ->will($this->returnValue($resultRequest));

        $executerMock = $this->getMockBuilder('PMSEExecuter')
                ->disableOriginalConstructor()
                ->setMethods(array('runEngine'))
                ->getMock();

        $preProcessorMock->setExecuter($executerMock);
        $preProcessorMock->setLogger($this->loggerMock);
        $preProcessorMock->setValidator($validatorMock);

        $preProcessorMock->processRequest($requestMock);
    }
}
