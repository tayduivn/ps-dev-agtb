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

/**
 * @coversDefaultClass \PMSEEngineUtils
 */
class PMSEEngineUtilsTest extends TestCase
{
    /**
     * @var PMSEEngineUtils
     */
    protected $object;
    /**
     * @var Dictionary
     */
    protected $oldDictionary;
    /**
     * @var Timedate
     */
    protected $oldTimedata;
    /**
     * @var Log
     */
    protected $oldLog;
    /**
     * @var ModuleList
     */
    protected $oldModuleList;
    /**
     * @var Current_user
     */
    protected $oldCurrentUser;
    /**
     * @var BeanList
     */
    protected $oldBeanList;
    /**
     * @var BwcModules
     */
    protected $oldBwcModules;
    /**
     * @var Request
     */
    protected $oldRequest;

    protected function setUp() : void
    {
        global $current_user, $beanList, $bwcModules;

        if (!empty($GLOBALS['dictionary'])) {
            $this->oldDictionary = $GLOBALS['dictionary'];
        }

        $GLOBALS['dictionary']['Email'] = array(
            'fields' => array(),
            'processes' => array(
                'enabled' => true,
                'types' => array(
                    'CF' => array('assigned_user_id'),
                    'AC' => array(),
                ),
            ),
        );

        if (!empty($GLOBALS['timedate'])) {
            $this->oldTimedata = $GLOBALS['timedate'];
        }
        $GLOBALS['timedate'] = TimeDate::getInstance();

        if (!empty($GLOBALS['log'])) {
            $this->oldLog = $GLOBALS['log'];
        }
        $levels = \LoggerManager::getLoggerLevels();
        $levels = array_keys($levels);
        $GLOBALS['log'] = $this->createPartialMock(\stdClass::class, $levels);

        if (!empty($GLOBALS['app_list_strings']['moduleList'])) {
            $this->oldModuleList = $GLOBALS['app_list_strings']['moduleList'];
        }
        $GLOBALS['app_list_strings']['moduleList'] = array('Emails' => 'Emails');

        if (!empty($_REQUEST)) {
            $this->oldRequest = $_REQUEST;
        }
        $_REQUEST = array('cardinality' => "all");

        $this->oldCurrentUser = $current_user;
        $this->oldBeanList = $beanList;
        $this->oldBwcModules = $bwcModules;

        \BeanFactory::setBeanClass('Emails', 'EmailMock');
    }

    protected function tearDown() : void
    {
        global $current_user, $beanList, $bwcModules;

        if (!empty($this->oldDictionary)) {
            $GLOBALS['dictionary'] = $this->oldDictionary;
        } else {
            unset($GLOBALS['dictionary']);
        }

        if (!empty($this->oldTimedata)) {
            $GLOBALS['timedate'] = $this->oldTimedata;
        } else {
            unset($GLOBALS['timedate']);
        }

        if (!empty($this->oldLog)) {
            $GLOBALS['log'] = $this->oldLog;
        } else {
            unset($GLOBALS['log']);
        }

        if (!empty($this->oldModuleList)) {
            $GLOBALS['app_list_strings']['moduleList'] = $this->oldModuleList;
        } else {
            unset($GLOBALS['app_list_strings']['moduleList']);
        }

        if (!empty($this->oldRequest)) {
            $_REQUEST = $this->oldRequest;
        } else {
            unset($_REQUEST);
        }

        $current_user = $this->oldCurrentUser;
        $beanList = $this->oldBeanList;
        $bwcModules = $this->oldBwcModules;

        \BeanFactory::unsetBeanClass('Emails');
    }


    /**
     * @covers PMSEEngineUtils::getModules
     * Emails module is a supported module in PMSE
     */
    public function testGetModules()
    {
        global $current_user, $beanList, $bwcModules;

        $this->object = $this->getMockBuilder('PMSEEngineUtils')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $current_user = $this->getMockBuilder(\User::class)
            ->setMethods(array('getDeveloperModules'))
            ->disableOriginalConstructor()
            ->getMock();

        $current_user->method('getDeveloperModules')
            ->will($this->returnValue(array('Emails')));

        $beanList['Emails'] = 'Email';
        $bwcModules = array('Employees', 'Documents');

        $supportedModules = $this->object->getModules();
        $this->assertArrayHasKey("Emails", $supportedModules, "Emails should be a supported module.");
    }

    /**
     * Test whether Emails is a supported module in different actions
     *
     * @covers PMSEEngineUtils::isSupportedModule
     * @dataProvider getIsSupportedModuleData
     * @param $callType
     * @param $isSupportedModule
     * @param $message
     */
    public function testIsSupportedModule($callType, $isSupportedModule, $message)
    {
        $this->object = $this->getMockBuilder('PMSEEngineUtils')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $_REQUEST['call_type'] = $callType;
        $supportedModule = $this->object->isSupportedModule('Email');
        if ($isSupportedModule === true) {
            $this->assertTrue($supportedModule, $message);
        } else {
            $this->assertFalse($supportedModule, $message);
        }
    }

    public function getIsSupportedModuleData()
    {
        return [
            [
                'callType' => '',
                'isSupportedModule' => true,
                'message' => 'callType is not set. Emails should be a supported module.',
            ],
            [
                'callType' => 'CF',
                'isSupportedModule' => true,
                'message' => 'Emails should be a supported module in CF.',
            ],
            [
                'callType' => 'AC',
                'isSupportedModule' => false,
                'message' => 'Emails should not be a supported module in AC.',
            ],
            [
                'callType' => 'moduleField',
                'isSupportedModule' => false,
                'message' => 'callType is set but not defined in processes of vardefs. ' .
                    'Emails should not be a supported module. ',
            ],
        ];
    }
}

class EmailMock
{
    public $field_defs = array();
    public function newBean($name)
    {
        return $this;
    }
}
