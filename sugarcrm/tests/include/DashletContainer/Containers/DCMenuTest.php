<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
require_once 'include/DashletContainer/Containers/DCAbstract.php';
require_once 'include/DashletContainer/Containers/DCMenu.php';

class DCMenuTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testGetMenuItem()
    {
        $dcMenu = new DCMenuMock();
        $menuItem = $dcMenu->getMenuItem('Accounts');
        $this->assertContains('icon_Accounts_bar_32.png', $menuItem, "Did not contain Accounts menu icon.");
        $this->assertContains('Create Account', $menuItem, "Did not contain Accounts create text.");
    }

}


class DCMenuMock extends DCMenu
{
    public function getMenuItem($module)
    {
        return parent::getMenuItem($module);
    }

    public function getDynamicMenuItem($def)
    {
        return parent::getDynamicMenuItem($def);
    }
}