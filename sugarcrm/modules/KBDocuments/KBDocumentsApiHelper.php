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

require_once('data/SugarBeanApiHelper.php');

/**
 * This class is here to add in the file information to the KBDocuments so that it can be easily displayed by remote consumers of the API. Otherwise you have to traverse a number of links to pull up this information.
 */
class KBDocumentsApiHelper extends SugarBeanApiHelper
{

    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array() )
    {
        $data = parent::formatForApi($bean, $fieldList, $options);

        if ( empty($fieldList) || in_array('attachment_list',$fieldList) ) {
            $db = DBManagerFactory::getInstance();

            $query = "SELECT rev.id rev_id, rev.filename filename, kbrev.id docrev_id FROM kbdocument_revisions kbrev LEFT JOIN document_revisions rev ON (kbrev.document_revision_id = rev.id) WHERE kbrev.kbdocument_id = '".$bean->id."' AND kbrev.deleted = 0 AND rev.deleted = 0 AND kbrev.kbcontent_id is NULL";
            $ret = $db->query($query,true);
            $files = array();
            while ( $row = $db->fetchByAssoc($ret) ) {
                $thisFile = array();
                $thisFile['document_revision_id'] = $row['rev_id'];
                $thisFile['document_revision_filename'] = $row['filename'];
                $thisFile['kbdocument_revision_id'] = $row['docrev_id'];
                $thisFile['file_uri'] = $this->api->getResourceURI(array('DocumentRevisions',$row['rev_id'],'file','filename'));
                $files[] = $thisFile;
            }
            $data['attachment_list'] = $files;
        }

        return $data;
    }
}
