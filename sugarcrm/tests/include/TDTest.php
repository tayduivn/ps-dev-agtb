<?php
require_once 'include/TimeDate.php';
require_once 'modules/Users/User.php';

class TDTest extends Sugar_PHPUnit_Framework_TestCase
{
	/**
	 * @var TimeDate
	 */
	protected $time_date;
	
	public function testMeeting1(){
		
		global $timedate;
		$today = gmdate($GLOBALS['timedate']->get_db_date_time_format(), time());		
		$newToday = $timedate->getNow()->asDb();
	
		
		$this->assertEquals($today,$newToday);
		
		
	}


	public function testMeeting2(){

		global $timedate;
		$nextday = gmdate($GLOBALS['timedate']->dbDayFormat, time() + 3600*24);
		$newNextDay = $timedate->asDbDate($timedate->getNow()->get("+1 day"));		

		
		$this->assertEquals($nextday,$newNextDay);
	


	}


	public function testModuleBuilder_parser_views_History(){

		$time =strtotime ( gmdate ( 'r' ) ) ;
		$newTime = strtotime(TimeDate::getInstance()->httpTime());		


		$this->assertEquals($time, $newTime);

	}


	public function testModuleBuilder_views_view_history(){
		
		
		global $timedate;
		$ts = strtotime(TimeDate::getInstance()->httpTime());
		
		$dbDate = gmdate ( $timedate->get_db_date_time_format (), $ts ) ;	
		$newDbDate = $timedate->fromTimestamp($ts)->asDb();

		$this->assertEquals($dbDate, $newDbDate);

	}

	public function testNotifications_Controller1(){

		
		global $timedate;	
		$thirtySecondAgo = time() - 30;
	        $thirtySecondsAgoFormatted = gmdate($timedate->get_db_date_time_format(), $thirtySecondAgo);
		$newFormat = $timedate->getNow()->get("30 seconds ago")->asDb();
		
	
		$this->assertEquals($thirtySecondsAgoFormatted, $newFormat);
	

	}

	public function testProducts_Product(){
		
		global $timedate;

		$support_expired='+1 month';
		$expired = date('Y-m-d', strtotime($support_expired));
		$newExpired = $timedate->asDbDate($timedate->getNow()->get($support_expired));		

	
		$this->assertEquals($expired, $newExpired);
	}


	public function testProjects_Convert1(){

		global $timedate;
		$today = date($GLOBALS['timedate']->dbDayFormat, time());
		
		$newToday = $timedate->nowDbDate();
	
		$this->assertEquals($today, $newToday);
	}


	public function testProjects_Convert2(){

		global $timedate;
		$nextWeek = date($GLOBALS['timedate']->dbDayFormat, time() + (7 * 24 * 60 * 60));

		$newNextWeek = $timedate->asDbDate( $timedate->getNow()->get('+1 week'));

		$this->assertEquals($nextWeek,$newNextWeek);
		
		

	}
	
	public function testProjects_layout_ProjectGrid_PDF(){

	
		$grid = date("m/d/Y", time());;
	
		$newGrid = TimeDate::getInstance()->nowDate();
	

		$this->assertEquals($grid, $newGrid);
	

	}


	public function testProject_ResouceReport(){

		global $timedate;

		$holiday = new Holiday();

		$holiday->holiday_date = $timedate->nowDate();
		$holidayDate = date($timedate->to_db_date($holiday->holiday_date, false));
		
		$newHolidayDate = $timedate->to_db_date($holiday->holiday_date);

		$holidays = $timedate->to_display_date($holidayDate, false, false);

		$newHolidays = $timedate->asUserDate($timedate->fromString($holidayDate));

		$this->assertEquals($holidayDate, $newHolidayDate);
		
		$this->assertEquals($holidays, $newHolidays);	

	}

	
	public function testProject_ResourceReport2(){

		global $timedate;
		
		$dateStart = $timedate->nowDb();

		
		$dateRangeArray = $timedate->to_display_date($dateStart, false, false);
	
		$newDateRangeArray = $timedate->asUserDate($timedate->fromString($dateStart));
		
		$this->assertEquals($dateRangeArray, $newDateRangeArray);

	

	}

	public function testProject_ResourcesReport3(){

		global $timedate;

		$dateStart = $timedate->nowDb();

		$dateStart1 = date($GLOBALS['timedate']->dbDayFormat, strtotime($dateStart) + 86400);
	
		$newDateStart = $timedate->asDbdate($timedate->fromString($dateStart)->get('+1 day'));
	
		$this->assertEquals($dateStart1,$newDateStart);

	}
	
	public function testProject_ResourcesReport4(){

		global $timedate;

		$dateStart =$timedate->nowDb();

		$displayDate = $timedate->to_display_date($dateStart, false, false);		

	}

	
	public function testProjects_MyProjectTasks(){

		global $timedate;
		$today = date($GLOBALS['timedate']->dbDayFormat);		
		$newToday = $timedate->nowDbDate();
		$this->assertEquals($today, $newToday);
	}

	
	public function testQueues_Seed(){

		global $timedate;
	
		$time = date($GLOBALS['timedate']->get_db_date_time_format());
		
		$now = $timedate->now();

		//_pp($now);		

		$newTime = $timedate->nowDb();

		
		//$this->assertEquals($time, $newTime);

	}

	public function testQuotes_Layouts_Invoices(){

		global $timedate;

		$time = $timedate->to_display_date(date($GLOBALS['timedate']->dbDayFormat, time()), false);
	
		

		$newTime = $timedate->asUserDate($timedate->getNow());

		$this->assertEquals($time, $newTime);

	}

	public function testReports_sugarpdfReports(){

		$date = date("Y-m-d H:m:s");

		$newDate = TimeDate::getInstance()->asDb(TimeDate::getInstance()->getNow());
		
		//_pp($date);

		//_pp($newDate);
	}	

//TimeDate::httpTime() == gmdate("D, d M Y H:i:s")


	public function testReportsTemplates(){

		$print_date = date("m/d/Y", time());
		$newPrintDate = TimeDate::getInstance()->nowDate();
		
		$this->assertEquals($print_date, $newPrintDate);
	}

	public function testScheduler_AddJobsHere(){
		
		global $timedate;

		$prune_interval = 1;	

		$timeStamp = db_convert("'".gmdate($GLOBALS['timedate']->get_db_date_time_format(),time()+(86400 * -$prune_interval))."'" ,"datetime");
	
	}



	public function testScheduler_EditView(){
	
		global $current_user;
		global $timedate;
		$current_user  = SugarTestUserUtilities::createAnonymousUser();
		$prefDate = $current_user->getUserDateTimePreferences();
        	$date_start = date($prefDate['date'], strtotime('2005-01-01'));
		$newStartDate = date($timedate->get_date_format($current_user), strtotime('2005-01-01'));	
		$start = $timedate->asUserDate($timedate->fromString('2005-01-01'));

		$this->assertEquals($date_start, $start);

	}


	public function testScheduler_SecurityAudit(){

		global $timedate;
		$date = mktime();
		$newDate = $timedate->asUserTs($timedate->getNow());

		//$this->assertEquals($date,$newDate);
	}

	public function testScheduler_JobThread(){

		
		$time = gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('now'));
		$newTime = TimeDate::getInstance()->nowDb();

		//$this->assertEquals($time, $newTime);

	}


	public function testScheduler_scheduler(){

		
		$lowerLimit = mktime(0, 0, 0, 1, 1, 2005);
		$newLowerLimit = TimeDate::getInstance()->asUserTs(TimeDate::getInstance()->fromString(2005-01-01));
		//_pp($lowerLimit);
		//_pp($newLowerLimit);


	}
	
	public function testScheduler(){
		
		$now = gmdate('Y-m-d H:i', strtotime('now'));
		
		//$time = TimeDate::getInstance()->fromDbFormat(TimeDate::getInstance()->getNow(),DB_DATE_FORMAT);

		//_pp($now);
		//_pp($time);

	}	

	public function testScheduler1(){


		$today  = getdate(gmmktime());
		$newToday = getdate(TimeDate::getInstance()->asUserTs(TimeDate::getInstance()->getNow()));

		//$this->assertEquals($today, $newToday);
		
	}


	public function testScheduler2(){

		global $timedate;

		$focus->date_time_start = $timedate->now();
		$startMon = date('m', strtotime($focus->date_time_start));
		//$newStartMon = $timedate->splitTime($focus->date_time_start, "m");	

		//_pp($startMon);
		//_pp($newStartMon);
	}


	public function testScheduler3(){

		$theDate = date('Y-m-d', strtotime('+1 day')); 
		$newDate = TimeDate::getInstance()->asDbDate(TimeDate::getInstance()->getNow()->get('+1 day'));
	

		$this->assertEquals($theDate, $newDate);
	}

	public function testScheduler4(){

		$theDate = date('Y-m-d');
		$newDate = TimeDate::getInstance()->nowDbDate();
	
		$this->assertEquals($theDate, $newDate);	
	}


	public function testScheduler5(){

		$date = gmdate('Y-m-d H:i', strtotime('now'));
		//_pp($date);
		
	}
	
	public function testSchedulerDaemon(){

		global $timedate;
		
		$time = date('H:i:s', strtotime('now'));
		//$newTime = $timedate->$asUser($timedate);
		//_pp($time);
		//_pp($newTime);

	}

	public function testSchedulerJobs(){

		global $timedate;
		
		$date = gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('Jan 01 2000 00:00:00'));

		$newDate = $timedate->asDb($timedate->fromString('Jan 01 2000 00:00:00'));

		//_pp($date);

		//_pp($newDate);
	}


	public function testTasks(){

		global $timedate;

		$today = $timedate->handle_offset(date($GLOBALS['timedate']->get_db_date_time_format(), time()), $timedate->dbDayFormat, true);

		
		$newToday = $timedate->nowDbDate();
		$this->assertEquals($today, $newToday);

	}

	public function testTasks_MyTasks(){

		global $timedate;
		$date = $timedate->to_display_date(date($GLOBALS['timedate']->dbDayFormat));	
		$newDate = $timedate->nowDate(); 

		
		$this->assertEquals($date, $newDate);
	}


	public function testTeamNotices_DefaultNotices(){

		global $timedate;
		$date = $timedate->to_display_date(date($GLOBALS['timedate']->dbDayFormat, time() + 86400 * 7));

		$newDate = $timedate->asUserDate($timedate->getNow()->get('+1 week'));
		$this->assertEquals($date, $newDate);
	}

	public function testTimePeriods_Save(){
		
		global $timedate;
		// $date = $timedate->to_db_date($timedate,false);
		//_pp($date);
	}	

	public function testTrackers(){

		global $timedate;

		$now = strtotime(date('D M Y'));
		//$newNow = $timedate->asUserTs($timedate->getNow());
		$lastYear = strtotime('01 February 2008');
   		$newLastYear = $timedate->fromString('01 February 2008');

		//_pp($lastYear);
		//_pp($newLastYear);

		//_pp( gmdate("Y-m-d H:i:s", rand($lastYear, $now)));
		//_pp( $timedate->asDb($timedate->fromTimestamp(rand($lastYear, $now))));
	}
	
	public function testUpgradeWizard_uw_ajax(){

		global $timedate;
		$nowDate = date('I');
		//_pp($nowDate);

	}
	
	public function testSoapFulTest(){
		
		
		global $timedate;
		
		$date = date($GLOBALS['timedate']->dbDayFormat, time() + rand(0,360000));
		//$newDate = $timedate->asDbDate($timedate->getNow()->get("+"+rand(0, 360000)+"seconds"));
		
	}
	
	public function testExpressions(){
		
	
		global $timedate;
		$time = date("w", time());
		//$newTime = $timedate->to_db($timedate->fromTimest(time()));
		_pp($time);
		//_pp($newTime);
		
		
	}
	
}





