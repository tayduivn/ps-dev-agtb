<?php
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

require_once 'data/SugarBeanApiHelper.php';

class DocumentsApiHelper extends SugarBeanApiHelper
{
    /**
     * Formats the bean so it is ready to be handed back to the API's client. Certian fields will get extra processing
     * to make them easier to work with from the client end.
     *
     * @param $bean SugarBean The bean you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Currently no options are supported
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        // If there was a field list requested and document_revision_id was not
        // a requested field we will have problems. Set the revision id so that 
        // additional fields like filename get picked up.
        if (!empty($fieldList) && !in_array('document_revision_id', $fieldList)) {
            $db = DBManagerFactory::getInstance();
            
            // Get the revision ID so that it can be set into the bean
            $sql = "SELECT document_revision_id 
                    FROM {$bean->table_name} 
                    WHERE id = '{$bean->id}'";
            
            $rs = $db->query($sql);
            $row = $db->fetchByAssoc($rs);
            if (isset($row['document_revision_id'])) {
                // Set the revision and setup everything else for a document that
                // depends on a revision id, like filename, revision, etc
                $bean->document_revision_id = $row['document_revision_id'];
                $bean->fill_in_additional_detail_fields();
            }
        }
        
        return parent::formatForApi($bean, $fieldList, $options);
    }
}
