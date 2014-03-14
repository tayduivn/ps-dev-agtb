<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

/**
 * @covers ExpressionEngineController
 */
class ExpressionEngine_ControllerTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        $_REQUEST = array();
        parent::tearDown();
    }

    /**
     * @param string $module
     * @param array  $fields
     * @param string $id
     *
     * @dataProvider badRequestProvider
     */
    public function testBadRequest($module, $fields, $id = null)
    {
        $_REQUEST = array(
            'tmodule' => $module,
            'fields' => $fields,
            'record_id' => $id,
        );

        require_once 'modules/ExpressionEngine/controller.php';

        /** @var PHPUnit_Framework_MockObject_MockObject | ExpressionEngineController $controller */
        $controller = $this->getMock('ExpressionEngineController', array('display'));

        // assert that display method was invoked which means no PHP error was triggered
        $controller->expects($this->once())->method('display');
        $controller->action_getRelatedValues();
    }

    public static function badRequestProvider()
    {
        return array(
            'non-json-string' => array('Accounts', 'non-json-string'),
            'bad-common-field-defs' => array('Accounts', json_encode(array(array()))),
            'bad-relate-field-defs' => array('Accounts', json_encode(array(array(
                'link' => 'foo',
                'type' => 'related',
            )))),
            'bad-rollup-field-defs' => array('Accounts', json_encode(array(array(
                'link' => 'foo',
                'type' => 'rollupSum',
            )))),
            'bean-not-found' => array('Accounts', json_encode(array(array(
                'link' => 'contacts',
                'type' => 'count',
            ))), 'non-existing-id'),
        );
    }
}
