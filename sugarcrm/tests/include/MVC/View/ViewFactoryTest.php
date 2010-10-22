<?php
require_once 'include/MVC/View/ViewFactory.php';

class ViewFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testLoadView()
    {
    	$view = ViewFactory::loadView('detail', 'Contacts');
    	$className = get_class($view);
    	$this->assertEquals($className,'ContactsViewDetail', 'Ensure that we load the right view for Contacts');
    }
}
