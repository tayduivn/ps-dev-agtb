<?php
//FILE SUGARCRM flav=pro ONLY
require_once('modules/SugarFavorites/SugarFavorites.php');
require_once('modules/SugarFavorites/controller.php');

class SugarFavoritesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }
    
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
    }
    
    public function testStarAndUnstarRecord()
    {
        $contactFocus = SugarTestContactUtilities::createContact();
        
        $_REQUEST['fav_module'] = 'Contacts';
        $_REQUEST['fav_id'] = $contactFocus->id;
        
        $controller = new SugarFavoritesController;
        $controller->loadBean();
        $controller->pre_save();
        $controller->action_save();
        
        $this->assertTrue(SugarFavorites::isUserFavorite($_REQUEST['fav_module'],$_REQUEST['fav_id']));
        
        $controller->action_delete();
        
        $this->assertFalse(SugarFavorites::isUserFavorite($_REQUEST['fav_module'],$_REQUEST['fav_id']));
    }
    
    public function testGetStarredRecordsForAModule()
    {
        $contactFocus = SugarTestContactUtilities::createContact();
        
        $_REQUEST['fav_module'] = 'Contacts';
        $_REQUEST['fav_id'] = $contactFocus->id;
        
        $controller = new SugarFavoritesController;
        $controller->loadBean();
        $controller->pre_save();
        $controller->action_save();
        
        $results = SugarFavorites::getUserFavoritesByModule($_REQUEST['fav_module']);
        
        $this->assertEquals($results[0]->record_id,$contactFocus->id);
        
        $controller->action_delete();
        
        $this->assertFalse(SugarFavorites::isUserFavorite($_REQUEST['fav_module'],$_REQUEST['fav_id']));
    }
}
