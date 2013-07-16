<?php

require_once('modules/ModuleBuilder/parsers/views/SidecarListLayoutMetaDataParser.php');

class SidecarListLayoutMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarListLayoutMetaDataParser
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = $this->getMock('SidecarListLayoutMetadataParser', array('handleSave'), array(), '', false);

        $implementation = $this->getMock(
            'DeployedMetaDataImplementation',
            array('getPanelDefsPath'),
            array(),
            '',
            false
        );

        $implementation->expects($this->any())
            ->method('getPanelDefsPath')
            ->will(
                $this->returnValue(
                    array('unittest', 'view', 'test')
                )
            );

        // set the visiblity on the parser to allow us to set the $implementation
        $pr = new ReflectionClass($this->parser);
        $prop = $pr->getProperty('implementation');
        $prop->setAccessible(true);
        $prop->setValue($this->parser, $implementation);

        $this->parser->client = 'unittest';
    }

    public function tearDown()
    {
        unset($this->parser);
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsFalseWhenViewDefsAreNotDefined()
    {
        $this->assertEmpty($this->parser->_viewdefs);
        $this->assertFalse(SugarTestReflection::callProtectedMethod($this->parser, 'getPanel'));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsFalseWhenViewDefsAreDefinedButEmpty()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array()
                    )
                )
            )
        );

        $this->assertFalse(SugarTestReflection::callProtectedMethod($this->parser, 'getPanel'));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsArrayWhenPanelIsDefined()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'unit' => 'test'
                            )
                        )
                    )
                )
            )
        );

        $expected = array(array('unit' => 'test'));

        $this->assertSame($expected, SugarTestReflection::callProtectedMethod($this->parser, 'getPanel'));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsSpecificPanelIfItExists()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'dummy' => 'panel'
                            ),
                            array(
                                'unit' => 'test'
                            )
                        )
                    )
                )
            )
        );

        $expected = array('unit' => 'test');

        $this->assertSame($expected, SugarTestReflection::callProtectedMethod($this->parser, 'getPanel', array(1)));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsAllPanelsIfSpecificPanelDoesNotExists()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'dummy' => 'panel'
                            ),
                            array(
                                'unit' => 'test'
                            )
                        )
                    )
                )
            )
        );

        $expected = array(
            array(
                'dummy' => 'panel'
            ),
            array(
                'unit' => 'test'
            )
        );

        $this->assertSame($expected, SugarTestReflection::callProtectedMethod($this->parser, 'getPanel', array(2)));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::addField()
     */
    public function testAddFieldAddsToEndOfPanel()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'fields' => array(
                                    array(
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ),
                                )
                            ),
                        )
                    )
                )
            )
        );

        $this->parser->_fielddefs = array(
            'test_field' => array(
                'label' => 'LBL_FIELD',
                'type' => 'text'
            )
        );

        $this->parser->addField('test_field');

        $panel = SugarTestReflection::callProtectedMethod($this->parser, 'getPanel');

        $this->assertEquals(2, count($panel[0]['fields']));

        $expected = array(
            'name' => 'test_field',
            'label' => 'LBL_FIELD',
            'enabled' => true,
            'default' => true,
            'sortable' => false
        );
        $this->assertSame($expected, array_pop($panel[0]['fields']));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::addField()
     */
    public function testAddFieldToStartOfPanel()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'fields' => array(
                                    array(
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ),
                                )
                            ),
                        )
                    )
                )
            )
        );

        $this->parser->_fielddefs = array(
            'test_field' => array(
                'label' => 'LBL_FIELD',
                'type' => 'text'
            )
        );

        $this->parser->addField('test_field', array(), 0, 0);

        $panel = SugarTestReflection::callProtectedMethod($this->parser, 'getPanel');

        $this->assertEquals(2, count($panel[0]['fields']));

        $expected = array(
            'name' => 'test_field',
            'label' => 'LBL_FIELD',
            'enabled' => true,
            'default' => true,
            'sortable' => false
        );
        $this->assertSame($expected, array_shift($panel[0]['fields']));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::addField()
     */
    public function testAddFieldAddsToMiddleOfPanel()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'fields' => array(
                                    array(
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ),
                                    array(
                                        'name' => 'address',
                                        'enabled' => true,
                                        'default' => true,
                                    ),
                                )
                            ),
                        )
                    )
                )
            )
        );

        $this->parser->_fielddefs = array(
            'test_field' => array(
                'label' => 'LBL_FIELD',
                'type' => 'text'
            )
        );

        $this->parser->addField('test_field', array(), 1, 0);

        $panel = SugarTestReflection::callProtectedMethod($this->parser, 'getPanel');

        $this->assertEquals(3, count($panel[0]['fields']));

        $expected = array(
            'name' => 'test_field',
            'label' => 'LBL_FIELD',
            'enabled' => true,
            'default' => true,
            'sortable' => false
        );
        $this->assertSame($expected, array_shift(array_splice($panel[0]['fields'], 1, 1)));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser:resetPanelFields()
     */
    public function testResetPanelFieldsRemovesAllFields()
    {
        $this->parser->_viewdefs = array(
            'unittest' => array(
                'view' => array(
                    'test' => array(
                        'panels' => array(
                            array(
                                'fields' => array(
                                    array(
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ),
                                    array(
                                        'name' => 'address',
                                        'enabled' => true,
                                        'default' => true,
                                    ),
                                )
                            ),
                        )
                    )
                )
            )
        );

        $this->parser->resetPanelFields();

        $this->assertEmpty($this->parser->_viewdefs['unittest']['view']['test']['panels'][0]['fields']);
    }
}
