<?php

require_once 'data/SugarACL.php';
require_once 'data/SugarBean.php';
require_once 'tests/tests/PHPUnit_Framework_SugarBeanRelated_TestCase.php';

/**
 * Test class for SugarACL getUserActions
 */
class SugarACLCreateTest extends PHPUnit_Framework_SugarBeanRelated_TestCase
{
    protected $bean;

    public function setUp()
    {
        SugarACL::$acls = array();
        if(!$this->bean)
        {
            $this->bean = $this->getTestMock();
        }
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $GLOBALS['beanList']['test'] = 'test';
        SugarTestHelper::setUp("current_user");
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
        SugarACL::$acls = array();
        unset($GLOBALS['dictionary'][$this->bean->object_name]);
        // clean cache so our experiments do not stay
        unset($_SESSION['ACL']);
    }

    public function getTestMock()
    {
        $bean = $this->getMockBuilder('MockSugarBeanACLCU')->disableOriginalConstructor()->getMock();
        $bean->model_name   = 'test';
        $bean->object_name  = 'test';
        $bean->module_dir   = 'test';
        $bean->expects($this->any())->method("bean_implements")->will($this->returnValue(true));

        return $bean;
    }

    public function modulesAccess()
    {
        return array(
            array("Users", array("access" => true, "view" => true, "import" => false, "create" => false)),
            array("Accounts", array("access" => true, "view" => true, "import" => true, "massupdate" => true, "create" => true)),
            array("test", array("access" => true, "view" => true, "import" => true, "create" => true)),
        );
    }

    /**
     * @dataProvider modulesAccess
     *
     * @param string $module
     * @param array $expected
     */
    public function testGetAccess($module, $expected)
    {
        $access = SugarACL::getUserAccess($module);
        foreach($expected as $action => $expvalue) {
            $this->assertEquals($access[$action], $expvalue, "Action $action for module $module should be: ".var_export($expvalue, true));
        }
    }

    public function testAccessDenied()
    {
        // load actions
        ACLAction::getUserActions($GLOBALS['current_user']->id, false, 'Accounts', 'module');
        // override module access
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Accounts']['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        $access = SugarACL::getUserAccess("Accounts");
        foreach(SugarACL::$all_access as $action => $value) {
            $this->assertFalse($access[$action], "Action $action should be set to false");
        }
    }

    public function testReadOnlyACL()
    {
        SugarACL::loadACLs("test");
        SugarACL::$acls["test"][] = new TestACLCreate();
        $access = SugarACL::getUserAccess("test");
        $this->assertFalse($access['create']);
    }

}

class TestACLCreate extends SugarACLStrategy
{
    public function checkAccess($module, $view, $context)
    {
        return $view != 'create';
    }
}

class MockSugarBeanACLCU extends SugarBean
{
    // do not let the mock kill defaultACLs function
    final public function defaultACLs()
    {
        return parent::defaultACLs();
    }
}