<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once 'tests/service/SOAPTestCase.php';
require_once('include/nusoap/nusoap.php');

/**
 * @group bug44931
 */
class Bug44931Test extends SOAPTestCase
{
	var $_soapClient = null;
	var $kbDocId = null;
	var $docRevisionId = null;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();

        global $app_list_strings;
        global $timedate;

        $app_list_strings = return_app_list_strings_language('en_us');

		$kbdoc = new KBDocument();
		$kbdoc->kbdocument_name = "Bug44931";
		$kbdoc->status_id = array_rand($app_list_strings['kbdocument_status_dom']);
		$kbdoc->team_id = $GLOBALS['current_user']->team_id;
		$kbdoc->assigned_user_id = $GLOBALS['current_user']->id;
		$kbdoc->active_date = $timedate->nowDb();
		$kbdoc->save();
		$this->kbDocId = $kbdoc->id;

		$kbdocRevision = new KBDocumentRevision;
		$kbdocRevision->revision = '1';
		$kbdocRevision->kbdocument_id = $kbdoc->id;
		$kbdocRevision->latest = true;
		$kbdocRevision->save();

		$docRevision = new DocumentRevision();
		$docRevision->filename = $kbdoc->kbdocument_name;
		$docRevision->save();
		$this->docRevisionId = $docRevision->id;

	    $kbdocContent = new KBContent();
	    $kbdocContent->document_revision_id = $docRevision->id;
	    $kbdocContent->team_id = $kbdoc->team_id;
		$kbdocContent->kbdocument_body = 'TEST!';
		$kbdocContent->save();

		$kbdocRevision->kbcontent_id = $kbdocContent->id;
	    $kbdocRevision->document_revision_id = $docRevision->id;
	    $kbdocRevision->save();

	    $kbdoc->kbdocument_revision_id = $kbdocRevision->id;
		$kbdoc->save();

	    $kbtag = new KBTag;
	    $kbtag->tag_name = 'Bug44931';
	    $id = $kbtag->save();

		$kbdocKBTag = new KBDocumentKBTag();
		$kbdocKBTag->kbtag_id = $kbtag->id;
		$kbdocKBTag->kbdocument_id = $kbdoc->id;
		$kbdocKBTag->team_id = $kbdoc->team_id;
		$kbdocKBTag->save();
        $GLOBALS['db']->commit();
		$this->useOutputBuffering = false;
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM document_revisions WHERE id = '{$this->docRevisionId}'");
        $GLOBALS['db']->query("DELETE FROM kbcontents WHERE document_revision_id = '{$this->docRevisionId}'");
        $GLOBALS['db']->query("DELETE FROM kbdocument_revisions WHERE kbdocument_id = '{$this->kbDocId}'");
        $GLOBALS['db']->query("DELETE FROM kbdocuments WHERE id = '{$this->kbDocId}'");
        $GLOBALS['db']->query("DELETE FROM kbdocuments_kbtags WHERE kbdocument_id = '{$this->kbDocId}'");
        $GLOBALS['db']->query("DELETE FROM kbtags WHERE tag_name = 'Bug44931'");
        parent::tearDown();
    }

    public function testGetEntryListForKBDocumentKBTagModule()
    {

        $this->_login();

        $parameters = array(
            'session' => $this->_sessionId,
            'module_name' => 'KBDocumentKBTags',
            'query' => "kbdocuments_kbtags.deleted=0 and kbdocuments_kbtags.kbdocument_id = '{$this->kbDocId}'",
            'order_by' => '',
            'offset' => 0,
            'select_fields' => array('id', 'kbdocument_id'),
            'max_results' => 250,
            'deleted' => 0,
            );

        $result = $this->_soapClient->call('get_entry_list',$parameters);

        $this->assertNotEmpty($result['field_list']);
        $this->assertEquals($this->kbDocId, $result['entry_list'][0]['name_value_list'][1]['value'], 'Assert we correctly queried by kbdocument_id');

    }

    /**
     * Attempt to login to the soap server
     *
     * @return $set_entry_result - this should contain an id and error.  The id corresponds
     * to the session_id.
     */
    public function _login()
    {
		global $current_user;

		$result = $this->_soapClient->call(
		    'login',
            array('user_auth' =>
                array('user_name' => $current_user->user_name,
                    'password' => $current_user->user_hash,
                    'version' => '.01'),
                'application_name' => 'SoapTest')
            );

        $this->_sessionId = $result['id'];

        return $result;
    }
}