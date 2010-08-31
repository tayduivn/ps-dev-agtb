<?php
// FILE SUGARCRM flav=pro ONLY 

require_once('modules/Reports/SearchFormReports.php');
require_once('include/ListView/ListViewSmarty.php');

class Bug39309Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $searchForm;
    
	public function setUp() 
    {
        $reportBean = new SavedReport();
		$this->searchForm = new SearchFormReports('Reports',$reportBean);

	}

	public function testSearchStripping()
	{
        $searchArray = array(
            'name'=>' abba ',
            );

        $this->searchForm->populateFromArray($searchArray,'advanced_search');
        $this->assertEquals('abba',$this->searchForm->searchFields['name']['value'],'Search string trimming is not working');
        
    }

	public function tearDown() 
    {
	}

}    