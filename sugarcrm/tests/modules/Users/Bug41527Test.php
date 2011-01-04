<?php

require_once 'modules/Users/User.php';

class Bug41527Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $_default_max_tabs_set = false;
    public $_default_max_tabs = '';
    public $_max_tabs_test = 666;

    public function setUp() 
    {
        $this->_default_max_tabs_set == isset($GLOBALS['sugar_config']['default_max_tabs']);
        if ($this->_default_max_tabs_set) {
            $this->_default_max_tabs = $GLOBALS['sugar_config']['default_max_tabs'];
        }

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['sugar_config']['default_max_tabs'] = $this->_max_tabs_test;
    }

    public function tearDown() 
    {
        if ($this->_default_max_tabs_set) {
            $GLOBALS['sugar_config']['default_max_tabs'] = $this->_default_max_tabs;
        } else {
            unset($GLOBALS['sugar_config']['default_max_tabs']);
        }
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    public function testUsingDefaultMaxTabsForOptionsValues() 
    {

        $_REQUEST['module'] = 'Users';
        $_REQUEST['action'] = 'EditView';
        
        require_once('include/MVC/Controller/ControllerFactory.php');
        require_once('include/MVC/View/ViewFactory.php');
        require_once('include/utils/layout_utils.php');
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        ob_start();
        $GLOBALS['app']->controller->execute();
        $html = ob_get_clean();

        $this->assertRegExp('/<select name="user_max_tabs".*<option label="' . $this->_max_tabs_test . '" value="' . $this->_max_tabs_test . '".*>' . $this->_max_tabs_test . '<\/option>.*<\/select>/ms', $html);
    }

}

