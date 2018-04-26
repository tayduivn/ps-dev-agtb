<?php
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
namespace Sugarcrm\SugarcrmTestUnit\modules\Quotes\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\MetaData\ViewdefManager;

/**
 * @coversDefaultClass \QuotesConfigApi
 */
class QuotesConfigApiTest extends TestCase
{
    protected $oldConfig = array();
    protected $moduleList = array();
    protected $oldStrings = array();

    protected function setUp()
    {
        require 'config.php';
        require 'include/language/en_us.lang.php';
        
        
        if (array_key_exists('sugar_config', $GLOBALS)) {
            $this->oldConfig = $GLOBALS['sugar_config'];
        }
        
        if (array_key_exists('moduleList', $GLOBALS)) {
            $this->moduleList = $GLOBALS['moduleList'];
        }

        if (array_key_exists('app_strings', $GLOBALS)) {
            $this->moduleList = $GLOBALS['app_strings'];
        }

        $GLOBALS['sugar_config'] = $sugar_config;
        $GLOBALS['moduleList'] = array();
        $GLOBALS['app_strings'] = $app_strings;
    }

    protected function tearDown()
    {
        $GLOBALS['sugar_config'] = $this->oldConfig;
        $GLOBALS['moduleList'] = $this->moduleList;
        $GLOBALS['app_strings'] = $this->oldStrings;
    }

    /**
     *
     * @covers ::configSave
     */
    public function testConfigSave()
    {
        $api = new \RestService();
        $userMock = $this->getMockBuilder('\User')
        ->setMethods([])
        ->disableOriginalConstructor()
        ->getMock();

        $userMock->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(true));
        
        $api->user = $userMock;
        $args = array(
            'module' => 'Quotes',
            'foo' => 'bar',
            'worksheet_columns' => [],
            'worksheet_columns_related_fields' => [],
        );

        $mock = $this->getMockBuilder('\QuotesConfigApi')
            ->setMethods(array('applyConfig'))
            ->disableOriginalConstructor()
            ->getMock();

        $refObject = new \ReflectionClass($mock);
        $refProp = $refObject->getProperty('skipMetadataRefresh');
        $refProp->setAccessible(true);
        $refProp->setValue($mock, true);

        $mock->expects($this->once())
            ->method('applyConfig');

        $config = $mock->configSave($api, $args);
        $this->assertEquals('bar', $config['foo']);
    }

    /**
     *
     * @param array $settings
     * @covers ::applyConfig
     * @dataProvider applyConfigProvider
     */
    public function testApplyConfig($settings, $expectException)
    {
        $quotesViewdef = array();
        $quotesViewdef['panels'][0]['fields'][1]['related_fields'][0]['fields'] = array(
            'name' => 'product_bundle_items',
            'fields' => array(),
        );
        $viewdefManagerMock = $this->getMockBuilder(ViewdefManager::class)
            ->setMethods(array('loadViewdef', 'saveViewdef'))
            ->disableOriginalConstructor()
            ->getMock();

        $viewdefManagerMock->expects($this->any())
            ->method('loadViewdef')
            ->will($this->returnValue($quotesViewdef));
        
        $mock = $this->getMockBuilder('\QuotesConfigApi')
        ->setMethods(array('getViewdefManager', 'getSettings'))
        ->disableOriginalConstructor()
        ->getMock();

        $mock->expects($this->any())
            ->method('getSettings')
            ->will($this->returnValue($settings));

        $mock->expects($this->any())
            ->method('getViewdefManager')
            ->will($this->returnValue($viewdefManagerMock));
        
        if ($expectException) {
            $this->expectException(\SugarApiExceptionInvalidParameter::class);
        } else {
            $viewdefManagerMock->expects($this->exactly(2))
                ->method('saveViewdef');
        }

        $mock->applyConfig();
    }

    public function applyConfigProvider()
    {
        return array(
            array(
                array(
                    'worksheet_columns_related_fields' => array(),
                ), true,
            ),
            array(
                array(
                    'worksheet_columns' => '',
                    'worksheet_columns_related_fields' => array(),
                ), true,
            ),
            array(
                array(
                    'worksheet_columns' => array(),
                    'worksheet_columns_related_fields' => array(),
                ), false,
            ),
            array(
                array(
                    'worksheet_columns' => array(),
                ), true,
            ),
            array(
                array(
                    'worksheet_columns' => array(),
                    'worksheet_columns_related_fields' => '',
                ), true,
            ),
        );
    }
}
