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

require_once 'clients/base/api/FileApi.php';

/**
 * API Class to handle temporary image (attachment) interactions with a field in
 * a bean that can be new, so no record id is associated yet.
 */
class FileTempApi extends FileApi {
    /**
     * Dictionary registration method, called when the API definition is built
     *
     * @return array
     */
    public function registerApiRest() {
        return array(
            'saveTempImagePost' => array(
                'reqType' => 'POST',
                'path' => array('<module>', 'temp', 'file', '?'),
                'pathVars' => array('module', 'temp', '', 'field'),
                'method' => 'saveTempImagePost',
                'rawPostContents' => true,
                'shortHelp' => 'Saves an image to a temporary folder.',
                'longHelp' => 'include/api/help/module_temp_file_field_post_help.html',
            ),
            'getTempImage' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'temp', 'file', '?', '?'),
                'pathVars' => array('module', 'record', '', 'field', 'temp_id'),
                'method' => 'getTempImage',
                'rawReply' => true,
                'shortHelp' => 'Reads a temporary image and deletes it.',
                'longHelp' => 'include/api/help/module_temp_file_field_temp_id_get_help.html',
            ),
        );
    }

    /**
     * Saves a temporary image to a module field using the POST method (but not attached to any model)
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return array
     * @throws SugarApiExceptionError
     */
    public function saveTempImagePost($api, $args) {
        if (!isset($args['record'])) {
            $args['record'] = null;
        }
        $temp = true;
        return $this->saveFilePost($api, $args, $temp);
    }

    /**
     * Gets a single temporary file for rendering and removes it from filesystem.
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return array
     */
    public function getTempImage($api, $args) {

        // Get the field
        if (empty($args['field'])) {
            // @TODO Localize this exception message
            throw new SugarApiExceptionMissingParameter('Field name is missing');
        }
        $field = $args['field'];

        // Get the bean
        $bean = BeanFactory::newBean($args['module']);

        //BEGIN SUGARCRM flav=pro ONLY
        // Handle ACL
        $this->verifyFieldAccess($bean, $field);
        //END SUGARCRM flav=pro ONLY

        $filepath = UploadStream::path("upload://tmp/") . $args['temp_id'];
        if (file_exists($filepath)) {
            $filedata = getimagesize($filepath);

            $info = array(
                'content-type' => $filedata['mime'],
                'content-length' => filesize($filepath),
                'path' => $filepath,
            );
            require_once "include/download_file.php";
            $dl = new DownloadFileApi($api);
            $dl->outputFile('image', $info);
            register_shutdown_function(function () use($filepath) { unlink($filepath); });
        } else {
            throw new SugarApiExceptionInvalidParameter('File not found');
        }
    }
}