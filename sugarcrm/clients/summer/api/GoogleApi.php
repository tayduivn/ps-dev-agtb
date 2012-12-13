<?php
//FILE SUGARCRM flav=free ONLY
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
class GoogleAPI extends SugarApi
{

    public function __construct()
    {
        $this->box = BoxOfficeClient::getInstance();
    }

    public function registerApiRest()
    {
        return array(
            'contacts' => array(
                'reqType' => 'GET',
                'path' => array('google', 'contacts'),
                'pathVars' => array('', ''),
                'method' => 'contacts',
                'shortHelp' => 'Recommended contacts',
            ),
            'recommend' => array(
                'reqType' => 'GET',
                'path' => array('google','recommend'),
                'pathVars' => array('',''),
                'method' => 'recommend',
                'shortHelp' => 'Recommended invites',
            ),
            'docs' => array(
                'reqType' => 'GET',
                'path' => array('google','docs'),
                'pathVars' => array('',''),
                'method' => 'docs',
                'shortHelp' => 'Show documents',
            ),
        );

    }


    protected function emailMatch($email, $domain)
    {
        list($efirst, $edomain) = explode('@', $email);
        if ($domain == $edomain) {
            return true;
        }
        return false;
    }

    public function contacts($api, $args)
    {
        global $current_user;
        $parts = $this->getEmailParts($current_user);
        return array("contacts" => $this->findContacts($parts[1]));
    }

    protected function findContacts($excludeDomain = '', $limit = 5, $maxDepth = 200)
    {
        $data = array();

        $res = $this->box->oauthGet("https://www.google.com/m8/feeds/contacts/default/full/?max-results={$maxDepth}&alt=json&orderby=lastmodified");
        $records = json_decode($res, true);

        foreach ($records['feed']['entry'] as $entry) {
            $email = '';
            $inv = array();

            $inv['first_name'] = (isset($entry['gd$name']) && isset($entry['gd$name']['gd$givenName'])) ? (string)$entry['gd$name']['gd$givenName']['$t'] : '';
            $inv['last_name'] = (isset($entry['gd$name']) && isset($entry['gd$name']['gd$familyName'])) ? (string)$entry['gd$name']['gd$familyName']['$t'] : '';

            if(!empty($entry['gd$email'])) {
                foreach ($entry['gd$email'] as $e) {
                    if (!empty($e['primary'])) {
                        $email = $e['address'];
                        break;
                    }
                }
            }

            $inv['image_uri'] = "";
            if(!empty($entry['link'])) {
                foreach ($entry['link'] as $link) {
                    // From Google Contacts API Docs (v3):
                    // If a contact does not have a photo, then the photo link element has no gd$etag attribute
                    if (isset($link['gd$etag'])) {
                        $inv['image_uri'] = $this->getContactImageURI($link['href']);
                        break;
                    }
                }
            }

            if (!empty($excludeDomain) && substr_count($email, $excludeDomain) == 1) continue;

            // TODO: Optimize this, somehow.
            $res = $GLOBALS['db']->query("SELECT COUNT(id) as x FROM email_addresses WHERE email_address = '" . $email . "'");
            $row = $GLOBALS['db']->fetchByAssoc($res);

            if ((int)$row['x'] > 0 || empty($email)) {
                continue;
            }

            $inv["email"] = (string)$email;
            $data[] = $inv;
        }

        shuffle($data);
        $data = array_slice($data, 0, $limit);

        return $data;
    }

    public function docs($api, $args) {
        $q = (!empty($args['q']))?$args['q']:'';
        $limit = (!empty($args['limit']))?$args['limit']:5;
        $email = (!empty($args['email']))?$args['email']:'';
        return array("docs" => $this->findDocuments($email, $q, $limit));
    }

    protected function findDocuments($email = '', $q = '', $limit = 5) {
        $data = array();
        $url = "https://www.googleapis.com/drive/v2/files?maxResults={$limit}&fields=items(id%2CembedLink%2CalternateLink%2Ctitle)&q=";
        $query = '(mimeType != "application/vnd.google-apps.folder")';
        if(!empty($q)) {
            $query .= ' AND (fullText contains "'.$q.'")';
        }
        if(!empty($email)) {
            $query .= ' AND ("'.$email.'" in writers OR "'.$email.'" in owners OR "'.$email.'" in readers)';
        }
        $url .= urlencode($query);
        $res = $this->box->oauthGet($url);
        $records = json_decode($res, TRUE);
        if(!empty($records['items'])) {
            foreach($records['items'] as $item) {
                if(empty($item['embedLink'])) $item['embedLink'] = $item['alternateLink'];
                $data[] = array(
                    'id' => $item['id'],
                    'name' => $item['title'],
                    'editLink' => $item['alternateLink'],
                    'previewLink' => $item['embedLink'],
                );
            }
        }
        return $data;
    }


    public function recommend($api, $args)
    {
        global $current_user;
        list($myfirst, $mydomain) = $this->getEmailParts($current_user);
        $contacts = array();
        if($mydomain && $mydomain != 'gmail.com') {
            $contacts = $this->findContacts('', 20);
        }

        $users = $this->box->getUserList();

        foreach ($contacts as $k => $v) {
            if(in_array($v['email'], $users)) {
                unset($contacts[$k]);
            }
        }

        return array("invites" => array_slice(array_values($contacts), 0, 5));
    }

    protected function getEmailParts($current_user) {
        $email = $current_user->email1;
        if(strpos($email, '@') === FALSE) {
            if(strpos($current_user->user_name, '@') !== FALSE) {
                $email = $current_user->user_name;
            } else {
                return array(FALSE, FALSE);
            }
        }
        return explode('@', $email);
    }

    protected function getContactImageURI($url) {
        $img_raw = $this->box->oauthGet($url);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $img_raw, FILEINFO_MIME_TYPE);
        finfo_close($f);
        return $img_uri = 'data:' . $mime_type . ';base64,' . base64_encode($img_raw);
    }
}
