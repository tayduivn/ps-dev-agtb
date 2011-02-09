<?php

require_once("modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php");
require_once("modules/ModuleBuilder/parsers/views/ListLayoutMetaDataParser.php");

class Bug42085Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $meeting;
	//var $listLayoutMetaDataParser;
	
	public function setUp()
	{
		$this->meeting = SugarTestMeetingUtilities::createMeeting();	
		//$this->listLayoutMetaDataParser = new ListLayoutMetaDataParser(MB_LISTVIEW, 'Meetings');
	}
	
	public function tearDown()
	{
		SugarTestMeetingUtilities::removeAllCreatedMeetings();
	}
	
    public function testHideMeetingType()
    {
    	$validDef = $this->meeting->field_defs['type'];
		$this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelesseditview'));
		$this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelessdetailview'));
    }

    public function testHideMeetingPassword()
    {
    	$validDef = $this->meeting->field_defs['password'];
		$this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelesseditview'));
		$this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelessdetailview'));
    } 

    public function testHideMeetingDisplayedURL()
    {
    	$validDef = $this->meeting->field_defs['displayed_url'];
		$this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelesseditview'));
		$this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelessdetailview'));
    }       
}

?>