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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/




require_once('tests/rest/RestTestBase.php');

class RestClearCacheTest extends RestTestBase {
    protected $_customFile = 'custom/include/api/PongApi.php';
    protected $_customDirMade = false;
    
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCache() {
        // This needs to be called before the custom dir is made
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
        
        if(!is_dir('custom/include/api')) {
            $this->_customDirMade = true;
            mkdir('custom/include/api',0777,true);
        }
        
        // Preapre the custom file
        $file_contents = <<<EOQ
<?php
class PongApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'pong' => array(
                'reqType' => 'GET',
                'path' => array('ping'),
                'pathVars' => array(''),
                'method' => 'pong',
                'shortHelp' => 'An example API only responds with ping',
                'longHelp' => 'include/api/html/ping_base_help.html',
            ),
            );
    }
    public function pong() {
        return 'ping';
    }
}
EOQ;
        file_put_contents($this->_customFile, $file_contents);
        // verify ping
        
        // verify pong isn't there
        $replyPong = $this->_restCall('ping');
        $this->assertNotEquals('ping', $replyPong['reply']);

        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->clearAdditionalCaches();
        $GLOBALS['current_user'] = $old_user;
        
        $this->assertTrue(!file_exists('cache/include/api/SugarApi/ServiceDictionary.rest.php'), "Didn't really clear the cache");


        // verify pong is there now
        $replyPong = $this->_restCall('ping');
        $this->assertEquals('ping', $replyPong['reply']);
        
        // Now undo it all and test again
        // Clean up after ourselves
        if (file_exists($this->_customFile)) {
            $dirname = dirname($this->_customFile);
            unlink($this->_customFile);
            
            if ($this->_customDirMade) {
                $done = rmdir($dirname);
            }
        }
        
        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->clearAdditionalCaches();
        $GLOBALS['current_user'] = $old_user;
        
        $this->assertTrue(!file_exists('cache/include/api/SugarApi/ServiceDictionary.rest.php'), "Didn't really clear the cache the SECOND time");
        
        // verify pong isn't there
        $replyPong = $this->_restCall('ping');
        $this->assertEquals('pong', $replyPong['reply']);
    }
}
