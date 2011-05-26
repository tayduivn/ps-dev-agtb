<?php

class Bug43159Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $contact;

    public function setUp()
    {

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public function tearDown()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}


    /*
     * @group bug43159
     */
    public function testSugarBeanSaveEmptyRecord()
    {
        //We want to ensure that manipulating the url directly does not result in an empty record being saved.
        //First take a count of all the leads in the db
        $result1 = $GLOBALS['db']->query('select count(id) as num FROM leads');
    	$row1 = $GLOBALS['db']->fetchByAssoc($result1);
    	$count1 = $row1['num'];


        //accessing the url directly to create the empty record, for example:
        //index.php?action=Save&module=Leads&record=&return_module=Leads
        //ends up creating a blank sugarbean and saving it.  Lets simulate the blank save
        ob_start();
        $lead = new Lead();
        $retStr = $lead->save();
        ob_end_clean();

        //take a count of the leads in db
        $result2 = $GLOBALS['db']->query('select count(id) as num FROM leads');
        $row2 = $GLOBALS['db']->fetchByAssoc($result2);
        $count2 = $row2['num'];

        //make sure that the counts have not changed.
        $this->assertEquals($count1, $count2, 'the number of leads has changed from '.$count1.' to '.$count2.' which means that the save was successful (it should not have been).');
    }
}
