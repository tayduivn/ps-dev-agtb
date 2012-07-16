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

require_once 'include/api/SugarApi/RestService.php';
require_once 'include/api/ThemeApi.php';

class ThemeApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp(){
        global $beanFiles, $beanList;
        require('include/modules.php');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
    }

    public function testCreateRelatedNote() {
        $contact = BeanFactory::getBean("Contacts");
        $contact->last_name = "Related Record Unit Test Contact";
        $contact->save();
        // Get the real data that is in the system, not the partial data we have saved
        $contact->retrieve($contact->id);
        $this->createdBeans[] = $contact;
        $noteName = "Related Record Unit Test Note";

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Contacts",
            "record" => $contact->id,
            "link_name" => "notes",
            "name" => $noteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['related_record']['id']);
        $this->assertEquals($noteName, $result['related_record']['name']);

        $note = BeanFactory::getBean("Notes", $result['related_record']['id']);
        // Get the real data that is in the system, not the partial data we have saved
        $note->retrieve($note->id);
        $this->createdBeans[] = $note;

        $contact->load_relationship("notes");
        $relatedNoteIds = $contact->notes->get();
        $this->assertNotEmpty($relatedNoteIds);
        $this->assertEquals($note->id, $relatedNoteIds[0]);
    }

    public function testGenerateBootstrapCss() {
        $api = new RestService();


        // TODO: is it necessary?
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "platform" => "Contacts",
            "custom" => null,
        );

        $apiClass = new ThemeApi();
        $result = $apiClass->generateBootstrapCss($api, $args);

        // TODO: verify that $result is the css file
    }

    public function testGetCustomThemeVars() {
        $api = new RestService();

        // TODO: is it necessary?
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "platform" => "base",
            "custom" => null,
        );

        $apiClass = new ThemeApi();
        $result = $apiClass->getCustomThemeVars($api, $args);

        $this->assertEquals($result["hex"], array(
            0   =>  array("name" => "primary",      "value" => "#E61718"),
            1   =>  array("name" => "secondary",    "value" => "#000000"),
            2   =>  array("name" => "primaryBtn",   "value" => "#177EE5"),
        ));
    }
}
