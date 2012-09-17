<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Summer invite API
 */
class SummerApi extends SugarApi {

    public function __construct()
    {
        $this->box = BoxOfficeClient::getInstance();
    }

    public function registerApiRest() {
        return array(
            'office' => array(
                'reqType' => 'GET',
                'path' => array('summer','office'),
                'pathVars' => array('',''),
                'method' => 'office',
                'shortHelp' => 'Office Surroundings',
            ),
            'recommend' => array(
                'reqType' => 'GET',
                'path' => array('summer','recommend'),
                'pathVars' => array('',''),
                'method' => 'recommend',
                'shortHelp' => 'Recommended invites',
            ),
            'contacts' => array(
                'reqType' => 'GET',
                'path' => array('summer','contacts'),
                'pathVars' => array('',''),
                'method' => 'contacts',
                'shortHelp' => 'Recommended contacts',
            ),
            'invite' => array(
                'reqType' => 'POST',
                'path' => array('summer','invite'),
                'pathVars' => array('',''),
                'method' => 'invite',
                'shortHelp' => 'Invite People',
            ),
            'logout' => array(
                'reqType' => 'POST',
                'path' => array('summer','logout'),
                'pathVars' => array('',''),
                'method' => 'logout',
                'shortHelp' => 'Log out of the instance',
            )
        );
    }

    public function office($api, $args)
    {
        return $this->box->getUsersInstances();
    }

    public function invite($api, $args)
    {
        if(!isset($args['email'])) {
            throw new SugarApiExceptionMissingParameter('Email is missing.');
        }
        return $this->box->invite($args['email']);
    }

    public function logout($api, $args)
    {
        $this->box->deleteSession();
        unset($_SESSION['authenticated_user_id']);
        return true;
    }

    protected function emailMatch($email, $domain)
    {
        list($efirst, $edomain) = explode('@', $email);
        if($domain == $edomain) {
            return true;
        }
        return false;
    }

    public function recommend($api, $args)
    {
        $data = array();
        $me = $this->box->getCurrentUser();
        list($myfirst, $mydomain) = explode('@', $me['email']);
        if($mydomain == 'gmail.com') {
            return array("invites" => array());
        }
        // FIXME: use memcache/local storage?
        if(!empty($_SESSION['recommended_invites'])) {
            return array("invites" => $_SESSION['recommended_invites']);
        }
        $res = $this->box->oauthGet("https://www.google.com/m8/feeds/contacts/default/full/");
        if(!empty($res)) {
            $xml = simplexml_load_string($res);
            $xml->registerXPathNamespace("gd", "http://schemas.google.com/g/2005");
            $xml->registerXPathNamespace("a", "http://www.w3.org/2005/Atom");
            foreach($xml->xpath("a:entry") as $entry) {
                $fname = $entry->xpath('gd:name/gd:givenName');
                $lname = $entry->xpath('gd:name/gd:familyName');
                $email = $entry->xpath('gd:email[@primary=\'true\']/@address');
                if(empty($email) || !$this->emailMatch((string)$email[0], $mydomain)) continue;
                $inv = array("email" => (string)$email[0], 'first_name' => '', 'last_name' => '');
                if(!empty($fname)) {
                    $inv['first_name'] = (string)$fname[0];
                }
                if(!empty($lname)) {
                    $inv['last_name'] = (string)$lname[0];
                }
                $data[] = $inv;
            }
        }
        $_SESSION['recommended_invites'] = $data;
        return array("invites" => $data);
    }

    public function contacts($api, $args)
    {
        $data = array();
        $me = $this->box->getCurrentUser();
        list($myfirst, $mydomain) = explode('@', $me['email']);
        $res = $this->box->oauthGet("https://www.google.com/m8/feeds/contacts/default/full/?max_results=10");
        if(!empty($res)) {
            $xml = simplexml_load_string($res);
            $xml->registerXPathNamespace("gd", "http://schemas.google.com/g/2005");
            $xml->registerXPathNamespace("a", "http://www.w3.org/2005/Atom");
            foreach($xml->xpath("a:entry") as $entry) {
                $fname = $entry->xpath('gd:name/gd:givenName');
                $lname = $entry->xpath('gd:name/gd:familyName');
                $email = $entry->xpath('gd:email[@primary=\'true\']/@address');
                $inv = array("email" => (string)$email[0], 'first_name' => '', 'last_name' => '');
                if(!empty($fname)) {
                    $inv['first_name'] = (string)$fname[0];
                }
                if(!empty($lname)) {
                    $inv['last_name'] = (string)$lname[0];
                }
                $data[] = $inv;
            }
        }
        return array("contacts" => $data);
    }

}
