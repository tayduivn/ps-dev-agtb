<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');

class Bug41493Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testGetListViewSmarty()
    {
        $teamset = new SugarFieldTeamset('teamset');
        $result_template = $teamset->getListViewSmarty(array('TEAM_NAME' => 'Team name'), array('name' => 'team_name'), array(), '');
        $this->assertRegExp('/Team name/', $result_template, 'lowercase name');
        $result_template = $teamset->getListViewSmarty(array('TEAM_NAME' => 'Team name'), array('name' => 'TEAM_NAME'), array(), '');
        $this->assertRegExp('/Team name/', $result_template, 'uppercase name');
    }
}