<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
class SugarLoggerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        // reset the logger level
        $level = SugarConfig::getInstance()->get('logger.level');
        if (!empty($level))
            $GLOBALS['log']->setLevel($level);
    }
    
    public function providerWriteLogEntries()
    {
        return array(
            array('debug','debug','foo1',true,'[DEBUG] foo1'),
            array('debug','info','foo2',true,'[INFO] foo2'),
            array('debug','warn','foo3',true,'[WARN] foo3'),
            array('debug','error','foo4',true,'[ERROR] foo4'),
            array('debug','fatal','foo5',true,'[FATAL] foo5'),
            array('debug','security','foo6',true,'[SECURITY] foo6'),
            array('fatal','warn','foo7',false,'[WARN] foo7'),
            );
    }
    
    /**
     * @dataProvider providerWriteLogEntries
     */
    public function testWriteLogEntries(
        $currentLevel,
        $logLevel,
        $logMessage,
        $shouldMessageBeWritten,
        $messageWritten
        ) 
    {
        $GLOBALS['log']->setLevel($currentLevel);
        $GLOBALS['log']->$logLevel($logMessage);
        
        $config = SugarConfig::getInstance();
        $ext = $config->get('logger.file.ext');
        $logfile = $config->get('logger.file.name');
        $log_dir = $config->get('log_dir'); 
        $log_dir = $log_dir . (empty($log_dir)?'':'/');
        
        $logFile = file_get_contents($log_dir . $logfile . $ext);
        
        if ( $shouldMessageBeWritten )
            $this->assertContains($messageWritten,$logFile);
        else
            $this->assertNotContains($messageWritten,$logFile);
    }
    
    public function testAssertLogging()
    {
        $GLOBALS['log']->setLevel('debug');
        $GLOBALS['log']->assert('this was asserted true',true);
        $GLOBALS['log']->assert('this was asserted false',false);
        
        $config = SugarConfig::getInstance();
        $ext = $config->get('logger.file.ext');
        $logfile = $config->get('logger.file.name');
        $log_dir = $config->get('log_dir'); 
        $log_dir = $log_dir . (empty($log_dir)?'':'/');
        
        $logFile = file_get_contents($log_dir . $logfile . $ext);
        
        $this->assertContains('[DEBUG] this was asserted false',$logFile);
        $this->assertNotContains('[DEBUG] this was asserted true',$logFile);
    }
}
