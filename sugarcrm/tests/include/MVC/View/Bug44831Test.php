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

class Bug44831Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        // Create a Custom editviewdefs.php
        sugar_mkdir("custom/modules/Leads/metadata/",null,true);

        if ( is_dir("cache/modules/Leads") )
            rmdir_recursive("cache/modules/Leads");

        if (file_exists("custom/modules/Leads/metadata/editviewdefs.php")) 
            unlink("custom/modules/Leads/metadata/editviewdefs.php");

        // Create a very simple custom EditView Layout
        if( $fh = @fopen("custom/modules/Leads/metadata/editviewdefs.php", 'w+') ) 
        {
$string = <<<EOQ
<?php
\$viewdefs['Leads']['EditView'] = array('templateMeta' => array (
                                                                 'form' => array('buttons' => array ('SAVE', 'CANCEL'),
                                                                                 'hidden' => array ('<a>HiddenPlaceHolder</a>',
                                                                                                   ),
                                                                                ),
                                                                 'maxColumns' => '2', 
                                                                 'useTabs' => true,
                                                                 'widths' => array( array ('label' => '10', 'field' => '30'),
                                                                                    array ('label' => '10', 'field' => '30'),
                                                                                  ),
                                                                 'javascript' => array( array ('file' => 'custom/modules/Leads/javascript/LeadJS1.js'),
                                                                                        array ('file' => 'custom/modules/Leads/javascript/LeadJS2.js'),
                                                                                      ),
                                                                ),
                                        'panels' => array ('default' => array (0 => array (0 => array ('name' => 'first_name',
                                                                                                      ),
                                                                                           1 => array ('name' => 'last_name',
                                                                                                      ),
                                                                                          ),
                                                                               1 => array (0 => array ('name' => 'unknown_field',
                                                                                                       'customCode' => '<a href="#">Unknown Field Link</a>',
                                                                                                      ),
                                                                                          ),
                                                                              ),
                                                          ),  
                                       );
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }


    }
    
    public function tearDown()
    {
        if ( is_dir("cache/modules/Leads") )
            rmdir_recursive("cache/modules/Leads");

        if (file_exists("custom/modules/Leads/metadata/editviewdefs.php")) 
            unlink("custom/modules/Leads/metadata/editviewdefs.php");

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
    }
    
    /**
    * @group bug44831
    */
    public function testJSInjection()
    {
    	$this->markTestIncomplete('Marked as skipped for now... too problematic');
    	return;
        require_once('include/utils/layout_utils.php');
        $_SERVER['REQUEST_METHOD'] = "POST";

        $lead = SugarTestLeadUtilities::createLead();
        $lead->name = 'LeadName';
        $lead->save();
        
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'EditView';
        $_REQUEST['record'] = $lead->id;
        
        require_once('include/MVC/Controller/ControllerFactory.php');
        require_once('include/MVC/View/ViewFactory.php');
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        //ob_start();
        $GLOBALS['app']->controller->execute();
        //$tStr = ob_get_clean();
        
        // First of all, need to be sure that I'm actually dealing with my new custom DetailView Layout
        $this->expectOutputRegex('/.*HiddenPlaceHolder.*/');
        // Then check inclusion of LeadJS1.js
        $this->expectOutputRegex('/.*<script src=\"custom\/modules\/Leads\/javascript\/LeadJS1\.js.*\"><\/script>.*/');
        // Then check inclusion of LeadJS2.js
        $this->expectOutputRegex('/.*<script src=\"custom\/modules\/Leads\/javascript\/LeadJS2\.js.*\"><\/script>.*/');
        
        //unset($GLOBALS['app']->controller);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }
}
