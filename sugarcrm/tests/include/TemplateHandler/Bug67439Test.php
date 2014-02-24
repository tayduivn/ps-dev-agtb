<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/TemplateHandler/TemplateHandler.php';


class Bug67439Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $oldObjectList;

    protected static $filesToUnlink = array(
        'cache/modules/Teams/EditView.tpl',
        'cache/modules/Teams/SearchForm_1.tpl',
        'cache/modules/Teams/DetailView.tpl',
    );

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('mod_strings', array('Teams'));

        $GLOBALS['beanFiles']['CustomTeam'] = 'custom/modules/Teams/Team.php';
        $GLOBALS['beanList']['Teams'] = 'CustomTeam';

        if (isset($GLOBALS['objectList'])) {
            static::$oldObjectList = $GLOBALS['objectList'];
        } else {
            $GLOBALS['objectList'] = static::$oldObjectList = array();
        }
        $GLOBALS['objectList']['Teams'] = 'Team';
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        $GLOBALS['objectList'] = static::$oldObjectList;
        foreach(static::$filesToUnlink as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @dataProvider dataProviderForBuildTemplate
     */
    public function testBuildTemplate($view, $metaDataDefs)
    {
        $templateHandler = $this->getMockBuilder('TemplateHandler')
            ->setMethods(array(
                'loadSmarty',
                'createQuickSearchCode',
                'createDependencyJavascript')
            )
            ->disableOriginalConstructor()
            ->getMock();
        $templateHandler->expects($this->any())
            ->method('createQuickSearchCode')
            ->with($this->equalTo($GLOBALS['dictionary']['Team']['fields']));
        $templateHandler->expects($this->any())
            ->method('createDependencyJavascript')
            ->with($this->equalTo($GLOBALS['dictionary']['Team']['fields']));

        $sugarSmarty = $this->getMockBuilder('Sugar_Smarty')
            ->setMethods(array('assign', 'fetch'))
            ->getMock();
        $sugarSmarty->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue('template content'));

        $templateHandler->ss = $sugarSmarty;

        $templateHandler->buildTemplate('Teams', $view, 'tpl', false, $metaDataDefs);
    }

    public function dataProviderForBuildTemplate()
    {
        $metaDataDefs = array(
            'panels' => array(
                array(
                    array(
                        array('name' => 'some_name'),
                        'other_name'
                    )
                )
            )
        );

        return array(
            array('EditView', $metaDataDefs),
            array('SearchForm_1', array()),
            array('DetailView', array())
        );
    }
}
