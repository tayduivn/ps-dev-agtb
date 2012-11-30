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
 *
 ********************************************************************************/

require_once('tests/rest/RestTestBase.php');

class RestTestPortalBase extends RestTestBase {

    protected $accounts = array();
    protected $contacts = array();
    protected $opps = array();
    protected $cases = array();
    protected $bugs = array();
    protected $notes = array();
    protected $kbdocs = array();
    protected $currentPortalBean = null;
    protected $testConsumer = null;
    protected $originalSetting = array();

    public function setUp()
    {
        // Setup the original settings
        if (empty($GLOBALS['system_config']->settings)) {
            $GLOBALS['system_config']->retrieveSettings();
        }
        
        if (isset($GLOBALS['system_config']->settings['supportPortal_RegCreatedBy'])) {
            $this->originalSetting['portaluserid'] = $GLOBALS['system_config']->settings['supportPortal_RegCreatedBy'];
        }
        
        if (isset($GLOBALS['system_config']->settings['portal_on'])) {
            $this->originalSetting['portalon'] = $GLOBALS['system_config']->settings['portal_on'];
        }
        
        parent::setUp();

        // Make the current user a portal only user
        $this->_user->portal_only = '1';
        $this->_user->save();
        
        // Reset the support portal user id to the newly created user id        
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', $this->_user->id);
        
        $this->role = $this->_getPortalACLRole();
        if (!($this->_user->check_role_membership($this->role->name))) {
            $this->_user->load_relationship('aclroles');
            $this->_user->aclroles->add($this->role);
            $this->_user->save();
        }

        // A little bit destructive, but necessary.
        $GLOBALS['db']->query("DELETE FROM contacts WHERE portal_name = 'unittestportal'");

        // Create the portal contact
        $this->contact = BeanFactory::newBean('Contacts');
        // Make the contact id unique-ish for test runs
        $this->contact->id = "UNIT-TEST-" . create_guid_section(10);
        $this->contact->new_with_id = true;
        $this->contact->first_name = "Little";
        $this->contact->last_name = "Unittest";
        $this->contact->description = "Little Unittest";
        $this->contact->portal_name = "unittestportal";
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash("unittest");
        $this->contact->save();

        $this->portalGuy = $this->contact;

        // Adding it to the contacts array makes sure it gets deleted when done
        $this->contacts[] = $this->contact;

        // Add the support_portal oauth key
        $this->testConsumer = BeanFactory::newBean('OAuthKeys');

        // use consumer to find bean with client_type === support portal
        $this->currentPortalBean = BeanFactory::newBean('OAuthKeys');
        $this->currentPortalBean->getByKey('support_portal', 'oauth2');
        $this->currentPortalBean->new_with_id = true;

        $GLOBALS['db']->query("DELETE FROM ".$this->testConsumer->table_name." WHERE client_type = 'support_portal'");

        // Create a unit test login ID
        $this->testConsumer->id = 'UNIT-TEST-portallogin';
        $this->testConsumer->new_with_id = true;
        $this->testConsumer->c_key = 'support_portal';
        $this->testConsumer->c_secret = '';
        $this->testConsumer->oauth_type = 'oauth2';
        $this->testConsumer->client_type = 'support_portal';
        $this->testConsumer->save();

        $GLOBALS['db']->commit();
    }
    public function tearDown()
    {
        // Cleaning up after ourselves, but only if there is cleanup to do
        // Accounts clean up
        if (count($this->accounts)) {
            $accountIds = array();
            foreach ( $this->accounts as $account ) {
                $accountIds[] = $account->id;
            }
            $accountIds = "('".implode("','",$accountIds)."')";
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
            }
        }

        // Opportunities clean up
        if (count($this->opps)) {
            $oppIds = array();
            foreach ( $this->opps as $opp ) {
                $oppIds[] = $opp->id;
            }
            $oppIds = "('".implode("','",$oppIds)."')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
            if ($GLOBALS['db']->tableExists('opportunities_cstm')) {
                $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
            }
        }

        // Contacts cleanup
        if (count($this->contacts)) {
            $contactIds = array();
            foreach ( $this->contacts as $contact ) {
                $contactIds[] = $contact->id;
            }
            $contactIds = "('".implode("','",$contactIds)."')";

            $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
            }
        }

        // Cases cleanup
        if (count($this->cases)) {
            $caseIds = array();
            foreach ( $this->cases as $aCase ) {
                $caseIds[] = $aCase->id;
            }
            $caseIds = "('".implode("','",$caseIds)."')";

            $GLOBALS['db']->query("DELETE FROM cases WHERE id IN {$caseIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_cases WHERE case_id IN {$caseIds}");
            if ($GLOBALS['db']->tableExists('cases_cstm')) {
                $GLOBALS['db']->query("DELETE FROM cases_cstm WHERE id_c IN {$caseIds}");
            }
        }

        // Bugs cleanup
        if (count($this->bugs)) {
            $bugIds = array();
            foreach( $this->bugs AS $bug ) {
                $bugIds[] = $bug->id;
            }
            $bugIds = "('" . implode( "','", $bugIds) . "')";
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id IN {$bugIds}");
            if ($GLOBALS['db']->tableExists('bugs_cstm')) {
                $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id_c IN {$bugIds}");
            }
        }

        // Notes cleanup
        if (count($this->notes)) {
            $noteIds = array();
            foreach ( $this->notes as $note ) {
                $noteIds[] = $note->id;
            }
            $noteIds = "('".implode("','",$noteIds)."')";

            $GLOBALS['db']->query("DELETE FROM notes WHERE id IN {$noteIds}");
            if ($GLOBALS['db']->tableExists('notes_cstm')) {
                $GLOBALS['db']->query("DELETE FROM notes_cstm WHERE id_c IN {$noteIds}");
            }
        }

        // KBDocs cleanup
        if (count($this->kbdocs)) {
            $kbdocIds = array();
            foreach ( $this->kbdocs as $kbdoc ) {
                $kbdocIds[] = $kbdoc->id;
            }
            $kbdocIds = "('".implode("','",$kbdocIds)."')";
            $GLOBALS['db']->query("DELETE FROM kbdocuments WHERE id IN {$kbdocIds}");
            if ($GLOBALS['db']->tableExists('kbdocuments_cstm')) {
                $GLOBALS['db']->query("DELETE FROM kbdocuments_cstm WHERE id_c IN {$kbdocIds}");
            }
        }

        // Delete test support_portal user
        $GLOBALS['db']->query("DELETE FROM ".$this->testConsumer->table_name." WHERE client_type = 'support_portal'");

        // Add back original support_portal user
        if(!empty($this->currentPortalBean->id)) {
            $this->currentPortalBean->save();
        }
        
        // reset the config table back to what it was originally, default if nothing was there
        $portalUserId = isset($this->originalSetting['portaluserid']) ? $this->originalSetting['portaluserid'] : '';
        $portalOn = empty($this->originalSetting['portalon']) ? '0' : '1';
        $GLOBALS['system_config']->saveSetting('supportPortal', 'RegCreatedBy', $portalUserId);
        $GLOBALS['system_config']->saveSetting('portal', 'on', $portalOn);
        $GLOBALS['db']->commit();
        parent::tearDown();
    }


    protected function _restLogin($username = '', $password = '')
    {
        $args = array(
            'grant_type' => 'password',
            'username' => 'unittestportal',
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        );

        // Prevent an infinite loop, put a fake authtoken in here.
        $this->authToken = 'LOGGING_IN';

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        if ( empty($reply['reply']['access_token']) ) {
            throw new Exception("Rest authentication failed, message looked like: ".$reply['replyRaw']);
        }
        $this->authToken = $reply['reply']['access_token'];
        $this->refreshToken = $reply['reply']['refresh_token'];
    }

    protected function _restLogout()
    {

        if (!empty($this->authToken) && !empty($this->refreshToken)) {
            $args = array(
                'token' => $this->authToken,
            );

            $reply = $this->_restCall('oauth2/logout',json_encode($args));
            if ( !isset($reply['reply']['success']) ) {
                throw new Exception("Rest logout failed, message looked like: ".$reply['replyRaw']);
            }
        }
    }

    // Copied from parser.portalconfig.php, when that gets merged we should probably just abuse that function.
    protected function _getPortalACLRole()
    {
        $allowedModules = array('Accounts','Bugs', 'Cases', 'Notes', 'KBDocuments', 'Contacts');
        $allowedActions = array('edit', 'admin', 'access', 'list', 'view');
        $role = new ACLRole();
        $role->retrieve_by_string_fields(array('name' => 'Customer Self-Service Portal Role'));
        $role->name = "Customer Self-Service Portal Role";
        $role->description = "Customer Self-Service Portal Role";
        $role->save();
        $GLOBALS['db']->commit();
        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed moduels
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
                $role->setAction($role->id, $actions['module']['admin']['id'], ACL_ALLOW_ALL);
                foreach ($actions['module'] as $actionName => $action) {
                    if (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    if ($moduleName == 'KBDocuments' && $actionName == 'edit') {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    if ($moduleName == 'Contacts') {
                        if ($actionName == 'edit' ) {
                            $aclAllow = ACL_ALLOW_OWNER;
                        }
                    }
                    if ($moduleName == 'Accounts' && $actionName == 'edit') {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }

        }
        return $role;
    }

}
