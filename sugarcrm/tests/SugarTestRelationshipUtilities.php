<?php
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
 ********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;

class SugarTestRelationshipUtilities
{
    private static $_relsAdded = array();

    protected static $_relRequiredKeys = array(
        'relationship_type',
        'lhs_module',
        'rhs_module',
    );

    /**
     * Create a relationship
     *
     * Params should be passed in as this:
     *
     * array(
     *       'relationship_type' => 'one-to-many',
     *       'lhs_module' => 'Accounts',
     *       'rhs_module' => 'Accounts',
     *   )
     *
     * @static
     * @param array $relationship_def
     * @return ActivitiesRelationship|bool|ManyToManyRelationship|ManyToOneRelationship|OneToManyRelationship|OneToOneRelationship
     */
    public static function createRelationship(array $relationship_def)
    {

        if(!self::checkRequiredFields($relationship_def)) return false;

        $relationships = new DeployedRelationships ($relationship_def['lhs_module']);

        if(!isset($relationship_def['view_module'])) {
            $relationship_def['view_module'] = $relationship_def['lhs_module'];
        }

        $REQUEST_Backup = $_REQUEST;

        $_REQUEST = $relationship_def;

        $relationship = $relationships->addFromPost();
        $relationships->save();
        $relationships->build();
        LanguageManager::clearLanguageCache($relationship_def['lhs_module']);

        SugarRelationshipFactory::rebuildCache();
        // rebuild the dictionary to make sure that it has the new relationship in it
        SugarTestHelper::setUp('dictionary');
        // reset the link fields since we added one
        VardefManager::$linkFields = array();

        $_REQUEST = $REQUEST_Backup;
        unset($REQUEST_Backup);


        self::$_relsAdded[] = $relationship->getDefinition();

        return $relationship;
    }

    /**
     * Remove all created relationships
     *
     * @static
     */
    public static function removeAllCreatedRelationships()
    {
        foreach(self::$_relsAdded as $rel) {

            $relationships = new DeployedRelationships($rel['lhs_module']);

            $relationships->delete($rel['relationship_name']);

            $relationships->save();
            $relationships->build();
            LanguageManager::clearLanguageCache($rel['lhs_module']);
            require_once("data/Relationships/RelationshipFactory.php");
            SugarRelationshipFactory::deleteCache();

            SugarRelationshipFactory::rebuildCache();
        }
        // since we are creating a relationship we need to unset this global var
        if(isset($GLOBALS['reload_vardefs'])) {
            unset($GLOBALS['reload_vardefs']);
        }
    }

    /**
     * Make sure we have at least the required keys
     *
     * @static
     * @param array $relationship_def
     * @return bool
     */
    protected static function checkRequiredFields(array $relationship_def)
    {
        foreach(self::$_relRequiredKeys as $key) {
            if(!array_key_exists($key, $relationship_def)) {
                return false;
            }
        }

        return true;
    }

}
?>