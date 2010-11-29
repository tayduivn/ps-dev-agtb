<?php
require_once 'modules/Calls/Call.php';

class CallTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Call our call object
     */
    private $callid;

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        if(!empty($this->callid)) {
            $GLOBALS['db']->query("DELETE FROM calls WHERE id={$this->callid}");
        }
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        @unlink($GLOBALS['sugar_config']['cache_dir'].'modules/Calls/language/test_test.lang.php');
    }

    public function testCallStatus()
    {
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->status = 'Test';
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('Test', $call->status);
    }

    public function testCallEmptyStatus()
    {
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('Planned', $call->status);
    }

    /**
     * @group bug40999
     * Check if empty status is handled correctly
     */
    public function testCallEmptyStatusLang()
    {
         file_put_contents($GLOBALS['sugar_config']['cache_dir'].'modules/Calls/language/test_test.lang.php',
              '<?php   $mod_strings=array("LBL_DEFAULT_STATUS" => \'FAILED!\'); ');
         $GLOBALS['current_language'] = 'test_test';
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('Planned', $call->status);
    }

    /**
     * @group bug40999
     * Check if empty status is handled correctly
     */
    public function testCallEmptyStatusLangConfig()
    {
         file_put_contents($GLOBALS['sugar_config']['cache_dir'].'modules/Calls/language/test_test.lang.php',
              '<?php   $mod_strings=array("LBL_DEFAULT_STATUS" => \'FAILED!\'); ');
         $GLOBALS['current_language'] = 'test_test';
         global $sugar_config;
         $sugar_config['default_call_status'] = "My Call";
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('My Call', $call->status);
    }
}