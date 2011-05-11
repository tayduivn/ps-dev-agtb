<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
require_once 'include/DashletContainer/DCFactory.php';

class DCFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testCanLoadContainerClass() 
    {
        $this->assertEquals(
            get_class(DCFactory::getContainer(null,'DCMenu')),
            'DCMenu'
            );
    }
    
    public function testCanLoadCustomContainerClass() 
    {
        $fileContents = <<<EOPHP
<?php

require_once('include/DashletContainer/Containers/DCMenu.php');

class CustomDCMenu extends DCMenu {}
EOPHP;
        sugar_mkdir('custom/include/DashletContainer/Containers',null,true);
        sugar_file_put_contents(
            'custom/include/DashletContainer/Containers/DCMenu.php',
            $fileContents);

        $this->assertEquals(
            get_class(DCFactory::getContainer(null,'DCMenu')),
            'CustomDCMenu'
            );
        
        unlink('custom/include/DashletContainer/Containers/DCMenu.php');
    }
    
    public function testDoNotLoadInvalidContainerClass() 
    {
        $this->assertFalse(DCFactory::getContainer(null,'SomeOtherDCMenu'));
    }
}
