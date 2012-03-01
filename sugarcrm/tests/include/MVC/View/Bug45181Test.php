<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/*
 * Bug 45181: Please remove "Log Memory Usage" if useless
 * @ticket 45181
 */

class Bug45181 extends Sugar_PHPUnit_Framework_TestCase {
    private $sugar_config;
    private $sugarView;

    function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        global $sugar_config;
        $this->sugar_config = $sugar_config;
        $this->sugarView = new Bug45181TestSugarViewMock();
        $this->sugarView->module = 'Contacts';
        $this->sugarView->action = 'EditView';
        if (is_file('memory_usage.log'))
        {
            unlink('memory_usage.log');
        }
    }

    function tearDown()
    {
        global $sugar_config;
        if (is_file('memory_usage.log'))
        {
            unlink('memory_usage.log');
        }
        $sugar_config = $this->sugar_config;
        unset($this->sugar_config);
        unset($GLOBALS['app_strings']);
    }


    /**
     * testLogMemoryUsageOn
     * This test asserts that when log_memory_usage is set to true we receive a log message from the function
     * call and the memory_usage.log file is created.
     *
     * @outputBuffering enabled
     */
    function testLogMemoryUsageOn()
    {
        if(!function_exists('memory_get_usage') || !function_exists('memory_get_peak_usage'))
        {
            $this->markTestSkipped('Skipping test since memory_get_usage and memory_get_peak_usage function are unavailable');
            return;
        }
        global $sugar_config;
        $sugar_config['log_memory_usage'] = true;
        $output = $this->sugarView->logMemoryStatisticsTest("\n");
        $this->assertNotEmpty($output, "Failed to recognize log_memory_usage = true setting");
        $this->assertFileExists('memory_usage.log', 'Unable to create memory_usage.log file');
    }

    /**
     * testLogMemoryUsageOff
     * This test asserts that when log_memory_usage is set to false we do not receive a log message from the function
     * call nor is the memory_usage.log file created.
     *
     * @outputBuffering enabled
     *
     */
    function testLogMemoryUsageOff()
    {
        if(!function_exists('memory_get_usage') || !function_exists('memory_get_peak_usage'))
        {
            $this->markTestSkipped('Skipping test since memory_get_usage and memory_get_peak_usage function are unavailable');
            return;
        }
        global $sugar_config;
        $sugar_config['log_memory_usage'] = false;
        $output = $this->sugarView->logMemoryStatisticsTest("\n");
        $this->assertEmpty($output, "Failed to recognize log_memory_usage = false setting");
        $this->assertFileNotExists('memory_usage.log');
    }
}

require_once('include/MVC/View/SugarView.php');
class Bug45181TestSugarViewMock extends SugarView
{
    public function logMemoryStatisticsTest($newline)
    {
        return $this->logMemoryStatistics($newline);
    }
}