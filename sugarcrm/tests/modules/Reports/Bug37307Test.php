<?php
require_once('modules/Reports/views/view.buildreportmoduletree.php');

class Bug37307Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testRelationshipWithApostropheInLabelOutputsCorrectly()
	{
		$bean_name = 'Foo';
		$link_module = 'Bar';
		$linked_field = array(
		    'name' => 'Dog',
		    'label' => "My Dog&#039;s",
		    'relationship' => 'Cat',
		    );
		
		$view = new MockReportsViewBuildreportmoduletree;
		$output = $view->_populateNodeItem($bean_name,$link_module,$linked_field);
		
		$this->assertEquals(
		    "javascript:SUGAR.reports.populateFieldGrid('Bar','Cat','Foo','My Dog\'s');",
		    $output['href']
		    );
	}
}

class MockReportsViewBuildreportmoduletree extends ReportsViewBuildreportmoduletree
{
    public function _populateNodeItem($bean_name,$link_module,$linked_field)
    {
        return parent::_populateNodeItem($bean_name,$link_module,$linked_field);
    }
}
