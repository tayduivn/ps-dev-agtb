<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


require_once 'modules/Notes/Note.php';
require_once 'include/SearchForm/SearchForm2.php';

/**
 * @group Bug45966
 */
class Bug45966 extends Sugar_PHPUnit_Framework_TestCase {

    var $module = 'Notes';
    var $action = 'index';
    var $seed;
    var $form;
    var $array;

    public function setUp() {
        require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;

        require "modules/".$this->module."/metadata/searchdefs.php";
        require "modules/".$this->module."/metadata/SearchFields.php";
        require "modules/".$this->module."/metadata/listviewdefs.php";

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('timezone', 'EDT');
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        $this->seed = new $beanList[$this->module];
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', "advanced_search", $listViewDefs);

        $this->array = array(
            'module'=>$this->module,
            'action'=>$this->action,
            'searchFormTab'=>'advanced_search',
            'query'=>'true',
            'date_entered_advanced_range_choice'=>'',
            'range_date_entered_advanced' => '',
            'start_range_date_entered_advanced' => '',
            'end_range_date_entered_advanced' => '',
        );
    }

    public function tearDown()
    {
        unset($this->array);
        unset($this->form);
        unset($this->seed);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testSearchDateEqualsAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = '12/31/2011';

        $this->array['date_entered_advanced_range_choice'] = '=';
        $this->array['range_date_entered_advanced'] = $testDate;

        $adjDate = $timedate->getDayStartEndGMT($testDate, $user);

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjDate['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ". $user->db->convert($user->db->quoted($adjDate['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchNotOnDateAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = '12/31/2011';

        $this->array['date_entered_advanced_range_choice'] = 'not_equal';
        $this->array['range_date_entered_advanced'] = $testDate;

        $adjDate = $timedate->getDayStartEndGMT($testDate, $user);

        $expected = "notes.date_entered IS NULL OR notes.date_entered < ". $user->db->convert($user->db->quoted($adjDate['start']), 'datetime').
            " OR ". strtolower($this->module).".date_entered > ". $user->db->convert($user->db->quoted($adjDate['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchAfterDateAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = '12/31/2011';

        $this->array['date_entered_advanced_range_choice'] = 'greater_than';
        $this->array['range_date_entered_advanced'] = $testDate;
        $adjDate = $timedate->getDayStartEndGMT($testDate, $user);

        $expected = "notes.date_entered > ".$user->db->convert($user->db->quoted($adjDate['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchBeforeDateAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = '01/01/2011';

        $this->array['date_entered_advanced_range_choice'] = 'less_than';
        $this->array['range_date_entered_advanced'] = $testDate;
        $adjDate = $timedate->getDayStartEndGMT($testDate, $user);

        $expected = "notes.date_entered < ".$user->db->convert($user->db->quoted($adjDate['start']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchLastSevenDaysAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'last_7_days';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true));
        $adjStartDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("-6 days"));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjStartDate['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjToday['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchNextSevenDaysAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'next_7_days';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true));
        $adjEndDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("+6 days"));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjToday['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjEndDate['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchLastThirtyDaysAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'last_30_days';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true));
        $adjStartDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("-29 days"));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjStartDate['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjToday['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchNextThirtyDaysAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'next_30_days';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true));
        $adjEndDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("+29 days"));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjToday['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjEndDate['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchLastMonthAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'last_month';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $now = $timedate->getNow(true);
        $month_number = $now->month == 1 ? 12 : $now->month-1;
        $year_number = $now->month == 1 ? $now->year - 1 : $now->year;
        $month = $now->get_day_begin(1, $month_number, $year_number);

        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin($month->days_in_month));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjThisMonthFirstDay['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjThisMonthLastDay['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchThisMonthAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'this_month';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $month = $timedate->getNow(true)->get_day_begin(1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin($month->days_in_month));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjThisMonthFirstDay['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjThisMonthLastDay['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchNextMonthAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'next_month';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, $now->month+1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin($month->days_in_month));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjThisMonthFirstDay['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjThisMonthLastDay['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchLastYearAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'last_year';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, 1, $now->year-1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjThisMonthFirstDay['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjThisMonthLastDay['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchThisYearAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'this_year';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $month = $timedate->getNow(true)->get_day_begin(1, 1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjThisMonthFirstDay['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjThisMonthLastDay['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchNextYearAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testDate = 'next_year';

        $this->array['date_entered_advanced_range_choice'] = $testDate;
        $this->array['range_date_entered_advanced'] = "[$testDate]";

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, 1, $now->year+1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12));

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjThisMonthFirstDay['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjThisMonthLastDay['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

    public function testSearchDateIsBetweenAdjustsForTimeZone() {
        global $timedate;
        $user = $GLOBALS['current_user'];

        $testStartDate = '01/01/2011';
        $testEndDate = '12/31/2011';

        $this->array['date_entered_advanced_range_choice'] = 'between';
        $this->array['start_range_date_entered_advanced'] = $testStartDate;
        $this->array['end_range_date_entered_advanced'] = $testEndDate;

        $adjStartDate = $timedate->getDayStartEndGMT($testStartDate, $user);
        $adjEndDate = $timedate->getDayStartEndGMT($testEndDate, $user);

        $expected = strtolower($this->module).".date_entered >= ".$user->db->convert($user->db->quoted($adjStartDate['start']), 'datetime').
        	" AND ". strtolower($this->module).".date_entered <= ".$user->db->convert($user->db->quoted($adjEndDate['end']), 'datetime');

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertContains($expected, $query[0]);
    }

}
