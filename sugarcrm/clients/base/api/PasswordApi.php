<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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

require_once('data/BeanFactory.php');
require_once('include/SugarFields/SugarFieldHandler.php');
require_once('include/api/SugarApi.php');


class PasswordApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'create' => array(
                'reqType' => 'GET',
                'path' => array('password', 'request'),
                'pathVars' => array('module'),
                'method' => 'requestPassword',
                'shortHelp' => 'This method sends email requests to reset passwords',
                'longHelp' => 'include/api/help/leads_register_post_help.html',
                'noLoginRequired' => true,
            ),
        );
    }

    /**
     * Resets password and sends email to user
     * @param $api
     * @param array $args
     * @return bool
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function requestPassword($api, $args)
    {
        require_once('modules/Users/language/en_us.lang.php');
        $res = $GLOBALS['sugar_config']['passwordsetting'];

        $requiredParams = array(
            'email',
            'username',
        );

        foreach ($requiredParams as $key => $param) {
            if (!isset($args[$param])) {
                throw new SugarApiExceptionMissingParameter('Error: Missing argument.', $args);
            }
        }

        $usr = empty($this->usr) ? new User() : $this->usr;
        $useremail = $args['email'];
        $username = $args['username'];

        if (!empty($username) && !empty($useremail)) {
            $usr_id = $usr->retrieve_user_id($username);
            $usr->retrieve($usr_id);

            if ($usr->email1 != $useremail) {
                throw new SugarApiExceptionRequestMethodFailure(translate(
                    'LBL_PROVIDE_USERNAME_AND_EMAIL',
                    'Users'
                ), $args);
            }

            if ($usr->portal_only || $usr->is_group) {
                throw new SugarApiExceptionRequestMethodFailure(translate(
                    'LBL_PROVIDE_USERNAME_AND_EMAIL',
                    'Users'
                ), $args);
            }
            // email invalid can not reset password
            if (!SugarEmailAddress::isValidEmail($usr->emailAddress->getPrimaryAddress($usr))) {
                throw new SugarApiExceptionRequestMethodFailure(translate('ERR_EMAIL_INCORRECT', 'Users'), $args);
            }

            $isLink = isset($args['link']) && $args['link'] == '1';
            // if i need to generate a password (not a link)
            $password = $isLink ? '' : User::generatePassword();

            // Create URL
            // if i need to generate a link
            if ($isLink) {
                $guid = create_guid();
                $url = $GLOBALS['sugar_config']['site_url'] . "/index.php?entryPoint=Changenewpassword&guid=$guid";
                $time_now = TimeDate::getInstance()->nowDb();
                $q = "INSERT INTO users_password_link (id, username, date_generated) VALUES('" . $guid . "','" . $username . "','" . $time_now . "') ";
                $usr->db->query($q);
            }

            if ($isLink && isset($res['lostpasswordtmpl'])) {
                $emailTemp_id = $res['lostpasswordtmpl'];
            } else {
                $emailTemp_id = $res['generatepasswordtmpl'];
            }

            $additionalData = array(
                'link' => $isLink,
                'password' => $password
            );

            if (isset($url)) {
                $additionalData['url'] = $url;
            }

            $result = $usr->sendEmailForPassword($emailTemp_id, $additionalData);

            if ($result['status']) {
                return true;
            } elseif ($result['message'] != '') {
                throw new SugarApiExceptionRequestMethodFailure($result['message'], $args);
            } else {
                throw new SugarApiExceptionRequestMethodFailure('LBL_EMAIL_NOT_SENT', $args);
            }

        } else {
            throw new SugarApiExceptionMissingParameter('Error: Empty argument', $args);
        }
    }

}
