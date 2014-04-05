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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
require_once "tests/upgrade/UpgradeTestCase.php";

class UpdateCustomBrokenViewLabelsTest extends UpgradeTestCase
{
    protected $viewdefs;

    public function setUp()
    {
        parent::setUp();
        $this->viewdefs = array(
            'foo'=>'bar',
            'label'=>'{$MOD.MY_LABEL||strip_semicolon}',
            'baz' => array(
                'foo'=>'bar',
                'label'=>'{$MOD.MY_LABEL||strip_semicolon}',
            ),
            'biz' => array(
                'foo' => 'bar',
                'label' => 'test test test',
            ),
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->viewdefs = null;
    }

    /**
     * Remove smarty template syntax viewdef labels, test that they are removed
     */
    public function testUpdateCustomBrokenViewLabels()
    {
        $script = $this->upgrader->getScript('post', '7_UpdateCustomBrokenViewLabels');
        $script->fixLabels($this->viewdefs);
        $this->checkViewdefs($this->viewdefs);
    }

    /**
     * @param $viewdefs viewdefs to check
     */
    public function checkViewdefs($viewdefs)
    {
        foreach ($viewdefs as $key => $val) {
            if (is_array($val)) {
                $this->checkViewdefs($val);
            } elseif ($key === 'label') {
                $this->assertTrue(strpos($val, 'strip_semicolon') === false);
            }
        }
    }
}
