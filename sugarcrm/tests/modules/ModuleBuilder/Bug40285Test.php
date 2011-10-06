<?php

require_once 'SugarTestUserUtilities.php';
require_once("modules/ModuleBuilder/controller.php");

class Bug40285Test extends Sugar_PHPUnit_Framework_TestCase
{
	
	public function setUp() 
    {
        echo "1\n";
        $_REQUEST [ 'view_module' ] =  'Accounts';
        $_REQUEST [ 'label' ] = 'LBL_REMOVEME';
        $_REQUEST [ 'labelValue' ] = 'removeme';
        $GLOBALS [ 'current_language' ] = 'en_us';
    }

    public function tearDown() 
    {
    }

    /**
     * @group bug40285
     */
    public function testLabelRemoval()
    {
        unset($_REQUEST [ 'view_package' ]);

        $controller = new ModuleBuilderController();

        $controller->action_SaveLabel();

        $lang_file = 'custom/modules/Accounts/language/en_us.lang.php';
        $this->assertFileExists($lang_file);

        unset($mod_strings);
        include($lang_file);

        $this->assertEquals('removeme', $mod_strings['LBL_REMOVEME']);

        $controller->DeleteLabel('en_us', 'LBL_REMOVEME', 'removeme', 'Accounts');

        unset($mod_strings);
        include($lang_file);

        $val = isset($mod_strings['LBL_REMOVEME'])? true: false;

        $this->assertFalse($val);
    }

}