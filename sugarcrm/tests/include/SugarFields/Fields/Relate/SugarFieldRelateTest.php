<?php 
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');

class SugarFieldRelateTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
	public function testFormatContactNameWithFirstName()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'contact_name');
	    $value = 'John Mertic';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            $sfr->formatField($value,$vardef),
            'Mertic John'
            );
    }
    
    /**
     * @group bug35265
     */
    public function testFormatContactNameWithoutFirstName()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'contact_name');
	    $value = 'Mertic';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            trim($sfr->formatField($value,$vardef)),
            'Mertic'
            );
    }
    
    /**
     * @group bug35265
     */
    public function testFormatContactNameThatIsEmpty()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'contact_name');
	    $value = '';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            trim($sfr->formatField($value,$vardef)),
            ''
            );
    }
    
    public function testFormatOtherField()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'account_name');
	    $value = 'John Mertic';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            $sfr->formatField($value,$vardef),
            'John Mertic'
            );
    }
}