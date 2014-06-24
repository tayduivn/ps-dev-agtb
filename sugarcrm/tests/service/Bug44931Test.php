<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once 'tests/service/SOAPTestCase.php';
require_once('vendor/nusoap//nusoap.php');

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

		$kbdoc = BeanFactory::getBean('KBDocuments');
		$kbdoc->kbdocument_name = "Bug44931";
		$kbdoc->status_id = array_rand($app_list_strings['kbdocument_status_dom']);
		$kbdoc->team_id = $GLOBALS['current_user']->team_id;
		$kbdoc->assigned_user_id = $GLOBALS['current_user']->id;
		$kbdoc->active_date = $timedate->nowDb();
		$kbdoc->save();
		$this->kbDocId = $kbdoc->id;

		$kbdocRevision = BeanFactory::getBean('KBDocumentRevisions');
		$kbdocRevision->revision = '1';
		$kbdocRevision->kbdocument_id = $kbdoc->id;
		$kbdocRevision->latest = true;
		$kbdocRevision->save();

		$docRevision = BeanFactory::getBean('DocumentRevisions');
		$docRevision->filename = $kbdoc->kbdocument_name;
		$docRevision->save();
		$this->docRevisionId = $docRevision->id;

	    $kbdocContent = BeanFactory::getBean('KBContents');
	    $kbdocContent->document_revision_id = $docRevision->id;
	    $kbdocContent->team_id = $kbdoc->team_id;
		$kbdocContent->kbdocument_body = 'TEST!';
		$kbdocContent->save();

		$kbdocRevision->kbcontent_id = $kbdocContent->id;
	    $kbdocRevision->document_revision_id = $docRevision->id;
	    $kbdocRevision->save();

	    $kbdoc->kbdocument_revision_id = $kbdocRevision->id;
		$kbdoc->save();

	    $kbtag = BeanFactory::getBean('KBTags');
	    $kbtag->tag_name = 'Bug44931';
	    $id = $kbtag->save();

		$kbdocKBTag = BeanFactory::getBean('KBDocumentKBTags');
		$kbdocKBTag->kbtag_id = $kbtag->id;
		$kbdocKBTag->kbdocument_id = $kbdoc->id;
		$kbdocKBTag->team_id = $kbdoc->team_id;
		$kbdocKBTag->save();
        $GLOBALS['db']->commit();
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