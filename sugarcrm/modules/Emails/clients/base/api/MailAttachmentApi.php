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

require_once "modules/Emails/EmailUI.php";

/**
 * API Class to handle file and image (attachment) interactions for an email.
 */
class MailAttachmentApi extends SugarApi
{
    /**
     * Dictionary registration method, called when the API definition is built
     *
     * @return array
     */
    public function registerApiRest()
    {
        return array(
            'saveAttachment' => array(
                'reqType' => 'POST',
                'path' => array('MailAttachment'),
                'pathVars' => array(''),
                'method' => 'saveAttachment',
                'rawPostContents' => true,
                'shortHelp' => 'Saves a mail attachment.',
                'longHelp' => '',
            ),
            'removeAttachment' => array(
                'reqType' => 'DELETE',
                'path' => array('MailAttachment', '?'),
                'pathVars' => array('', 'file_guid'),
                'method' => 'removeAttachment',
                'rawPostContents' => true,
                'shortHelp' => 'Removes an attached file',
                'longHelp' => '',
            ),
            'clearUserCache' => array(
                'reqType' => 'DELETE',
                'path' => array('MailAttachment', 'cache'),
                'pathVars' => array('', ''),
                'method' => 'clearUserCache',
                'rawPostContents' => true,
                'shortHelp' => 'Clears the user\'s attachment cache directory',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Saves an email attachment using the POST method
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return array metadata about the attachment including name, guid, and nameForDisplay
     */
    public function saveAttachment($api, $args)
    {
        $email = new Email();
        $email->email2init();
        $metadata = $email->email2saveAttachment();
        return $metadata;
    }

    /**
     * Removes an email attachment
     *
     * @param ServiceBase $api The service base
     * @param array $args The request args
     * @return bool
     */
    public function removeAttachment($api, $args)
    {
        $email = new Email();
        $email->email2init();
        $fileGUID = $args['file_guid'];
        $fileName = $email->et->userCacheDir . "/" . $fileGUID;
        $filePath = clean_path($fileName);
        unlink($filePath);
        return true;
    }

    /**
     * Clears the user's attachment cache directory
     *
     * @param ServiceBase $api The service base
     * @param array $args The request args
     * @return bool
     */
    public function clearUserCache($api, $args)
    {
        $em = new EmailUI();
        $em->preflightUserCache();
        return true;
    }
}
