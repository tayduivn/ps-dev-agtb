<?php
require_once "modules/KBDocuments/SearchUtils.php";

class SearchUtilsTest extends Sugar_PHPUnit_Framework_TestCase
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
    
    /**
     * @group bug41574
     */
    public function testGetQSAuthorUsesPassedFormname()
    {
        $qsArray = getQSAuthor('testme');
        
        $this->assertEquals($qsArray['form'],'testme');
    }
    
    /**
     * @group bug41574
     */
    public function testGetQSApproverUsesPassedFormname()
    {
        $qsArray = getQSApprover('testme');
        
        $this->assertEquals($qsArray['form'],'testme');
    }
    
    /**
     * @group bug41574
     */
    public function testGetQSTagsUsesPassedFormname()
    {
        $qsArray = getQSTags('testme');
        
        $this->assertEquals($qsArray['form'],'testme');
    }
}
