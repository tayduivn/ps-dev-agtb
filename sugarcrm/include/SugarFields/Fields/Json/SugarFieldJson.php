<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
 * SugarFieldTeamset.php
 * 
 * This class handles the processing of the new Team selection widget.
 * The main thing to note is that the getDetailViewSmarty, getEditViewSmarty and
 * getSearchViewSmarty methods are called from the cached .tpl files that are generated 
 * via the MVC/Metadata framework.  The cached .tpl files include Smarty code rendered from
 * the include/SugarFields/Fields/SugarFieldTeamset/Teamset.tpl file which in turn
 * calls this file.  When the plugin function is run (see include/Smarty/plugins/function.sugarvar_teamset.php), 
 * it will call SugarFieldTeamset's render method.  From there, the corresponding method is invoked.
 * 
 * For the MassUpdate section, there is no cached .tpl file created so the contents are rendered without
 * using the Teamset.tpl approach.
 * 
 * For classic views (where PHP files use the XTemplate processing) we provide the
 * getClassicView method.  Also note, the getClassicViewQS method.  For some classic views,
 * we use this method in situations where the quick search sections need to be generated 
 * separately from the widget code.
 *
 */

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldJson extends SugarFieldBase {

    // Here are the functions used by the REST API
    /**
     * This function will pull out the various teams in this teamset and return them in a collection
     * 
     * @param array     $data
     * @param SugarBean $bean
     * @param array     $args
     * @param string    $fieldName
     * @param array     $properties
     */
    public function apiFormatField(array &$data, SugarBean $bean, array $args, $fieldName, $properties) {
        require_once('modules/Teams/TeamSetManager.php');
        $teamList = TeamSetManager::getUnformattedTeamsFromSet($bean->team_set_id);
        if ( ! is_array($teamList) ) {
            // No teams on this bean yet.
            $teamList = array();
        }
        foreach ( $teamList as $idx => $team ) {
            if ($team['id']==$bean->team_id) {
                $teamList[$idx]['primary'] = true;
            } else {
                $teamList[$idx]['primary'] = false;
            }
        }
        $data[$fieldName] = $teamList;

        // These are just confusing to people on the other side of the API
        unset($data['team_set_id']);
        unset($data['team_id']);
    }

	/**
     * This function handles turning the API's version of a teamset into what we actually store
     * @param SugarBean $bean - the bean performing the save
     * @param array $params - an array of paramester relevant to the save, which will be an array passed up to the API
     * @param string $fieldName - The name of the field to save (the vardef name, not the form element name)
     * @param array $properties - Any properties for this field
     */
    public function apiSave(SugarBean $bean, array $params, $fieldName, $properties) {
        // Find the primary team id, or the first one, if nothing is set to primary
        $teamList = $params[$fieldName];
        if (!is_array($teamList)) {
            $teamList = array();
        }
        $teamIds = array();
        foreach ( $teamList as $idx => $team ) {
            //For empty array
            if(!isset($team['id'])) continue;
            if ( isset($team['primary']) && $team['primary'] == true ) {
                $primaryTeamId = $team['id'];
            }
            $teamIds[] = $team['id'];
        }
        if ( count($teamIds) == 0 ) {
            // There are no teams being set, set the defaults and move on
            $bean->setDefaultTeam();
            return;
        }
        if ( !isset($primaryTeamId) ) {
            // They didn't specify a primary team, so I'm just going to set it to the first one
            $primaryTeamId = $teamIds[0];
        }
        $bean->team_id = $primaryTeamId;
        
        $bean->load_relationship('teams');
        $method = 'replace';
        $bean->teams->replace($teamIds, array(), false);
    }

    
}
?>