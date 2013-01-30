<?php

require_once 'data/SugarBean.php';
require_once 'modules/ACLActions/ACLAction.php';

/**
 * Test class for ACLActions
 */
class ACLActionTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean;

    public function setUp()
    {
        $this->bean = new ACLAction;
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp("current_user");

    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
        SugarACL::$acls = array();
        unset($this->bean);
        // clean cache so our experiments do not stay
        unset($_SESSION['ACL']);
    }

    public function childAccess()
    {
        return array(
            array("list", array("export")),
            array("massupdate", array()),
            array("delete", array()),
            array("i-am-not-a-real-action-hero", array()),
        );
    }

    public function parentAccess()
    {
        return array(
            array("list", array("view")),
            array("massupdate", array("view")),
            array("delete", array("edit","view")),
            array("i-am-not-a-real-action-hero", array()),
        );
    }

    /**
     * @dataProvider childAccess
     *
     * @param string $action_name
     * @param array $expected
     */
    public function testGetChild($action_name, $expected)
    {
        $this->assertEquals($this->bean->getChildActions($action_name), $expected);
    }

    /**
     * @dataProvider parentAccess
     *
     * @param string $action_name
     * @param array $expected
     */
    public function testGetParent($action_name, $expected)
    {
        $this->assertEquals($this->bean->getParentActions($action_name), $expected);
    }


}