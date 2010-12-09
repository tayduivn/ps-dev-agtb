<?php
require_once 'include/MVC/View/SugarView.php';

class SugarViewTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testGetModuleTab()
    {
        $view = new SugarViewTestMock();
        $_REQUEST['module_tab'] = 'ADMIN';
        $moduleTab = $view->getModuleTab();
        $this->assertEquals('ADMIN', $moduleTab, 'Module Tab names are not equal from request');
    }

    public function testGetMetaDataFile()
    {
        $view = new SugarViewTestMock();
        $view->module = 'Contacts';
        $view->type = 'detail';
        $metaDataFile = $view->getMetaDataFile();
        $this->assertEquals('modules/Contacts/metadata/detailviewdefs.php', $metaDataFile, 'Did not load the correct metadata file');

        //test custom file
        sugar_mkdir('custom/modules/Contacts/metadata/', null, true);
        $customFile = 'custom/modules/Contacts/metadata/detailviewdefs.php';
        if(!file_exists($customFile))
        {
            sugar_file_put_contents($customFile, array());
            $customMetaDataFile = $view->getMetaDataFile();
            $this->assertEquals($customFile, $customMetaDataFile, 'Did not load the correct custom metadata file');
            unlink($customFile);
        }
    }

}

class SugarViewTestMock extends SugarView
{
    public function getModuleTab()
    {
        return parent::_getModuleTab();
    }
}
