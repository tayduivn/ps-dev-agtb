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

require_once 'modules/ModuleBuilder/parsers/parser.dropdown.php';

/**
 * @covers ParserDropDown
 */
class ParserDropDownTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $corePath = 'include/language/';
    protected $customPath = 'custom/include/language/';
    protected $parser;

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile("{$this->corePath}{$GLOBALS['current_language']}.lang.php");
        SugarTestHelper::saveFile("{$this->customPath}{$GLOBALS['current_language']}.lang.php");
        sugar_cache_clear("app_strings.{$GLOBALS['current_language']}");
        sugar_cache_clear("app_list_strings.{$GLOBALS['current_language']}");

        $this->parser = new ParserDropDown();
    }

    public function tearDown()
    {
        SugarTestLanguageFileUtilities::clearCache();
        SugarTestHelper::tearDown();

        parent::tearDown();
    }

    /**
     * @covers ParserDropDown::getDropDowns
     */
    public function testGetDropDowns_ReturnsCoreDropDowns()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
    'meeting_status_dom' => array(
        'Planned' => 'Planned',
        'Held' => 'Held',
        'Not Held' => 'Not Held',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'To Do',
        'Email' => 'Email',
        'Note' => 'Note',
        'SMS' => 'Text Message',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $actual = $this->parser->getDropDowns("{$this->corePath}{$GLOBALS['current_language']}.lang.php");

        $this->assertArrayHasKey('activity_dom', $actual);
        $this->assertArrayHasKey('meeting_status_dom', $actual);
        $this->assertEquals('Task', $actual['activity_dom']['Task']);
    }

    /**
     * @covers ParserDropDown::getDropDowns
     */
    public function testGetDropDowns_ReturnsCustomDropDowns()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
    'meeting_status_dom' => array(
        'Planned' => 'Planned',
        'Held' => 'Held',
        'Not Held' => 'Not Held',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'To Do',
        'Email' => 'Email',
        'Note' => 'Note',
        'SMS' => 'Text Message',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $actual = $this->parser->getDropDowns("{$this->customPath}{$GLOBALS['current_language']}.lang.php");

        $this->assertArrayHasKey('activity_dom', $actual);
        $this->assertArrayNotHasKey('meeting_status_dom', $actual);
        $this->assertEquals('To Do', $actual['activity_dom']['Task']);
    }

    public function getDropDownsRestrictedDropDownsAreIgnoredProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @covers ParserDropDown::getDropDowns
     * @dataProvider getDropDownsRestrictedDropDownsAreIgnoredProvider
     * @param $isCustom
     */
    public function testGetDropDowns_RestrictedDropDownsAreIgnored($isCustom)
    {
        $core = <<<EOF
\$app_list_strings = array(
    'eapm_list' => array(
        'Sugar' => 'Sugar',
        'WebEx' => 'WebEx',
        'GoToMeeting' => 'GoToMeeting',
        'IBMSmartCloud' => 'IBM SmartCloud',
        'Google' => 'Google',
        'Box' => 'Box.net',
        'Facebook' => 'Facebook',
        'Twitter' => 'Twitter',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'eapm_list' => array(
        'Foo' => 'Bar',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $prefix = $isCustom ? $this->customPath : $this->corePath;
        $actual = $this->parser->getDropDowns("{$prefix}{$GLOBALS['current_language']}.lang.php");

        $this->assertEmpty($actual);
    }

    /**
     * @covers ParserDropDown::getDropDowns
     */
    public function testGetDropDowns_FileDoesNotExist_ReturnsAnEmptyArray()
    {
        $actual = $this->parser->getDropDowns('./foobar');

        $this->assertEmpty($actual);
    }

    /**
     * @covers ParserDropDown::getDropDowns
     */
    public function testGetDropDowns_GLOBALSIsUsedInTheCustomizations_ReturnsCustomDropDowns()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
    'meeting_status_dom' => array(
        'Planned' => 'Planned',
        'Held' => 'Held',
        'Not Held' => 'Not Held',
    ),
);

EOF;

        $custom = <<<EOF
\$GLOBALS['app_list_strings']['activity_dom'] = array(
    'Call' => 'Call',
    'Meeting' => 'Meeting',
    'Task' => 'To Do',
    'Email' => 'Email',
    'Note' => 'Note',
    'SMS' => 'Text Message',
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $actual = $this->parser->getDropDowns("{$this->customPath}{$GLOBALS['current_language']}.lang.php");

        $this->assertArrayHasKey('activity_dom', $actual);
        $this->assertArrayNotHasKey('meeting_status_dom', $actual);
        $this->assertEquals('To Do', $actual['activity_dom']['Task']);
    }
}
