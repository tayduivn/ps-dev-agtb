<?PHP
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class DBFTS extends Basic
{
    var $new_schema = true;
    var $module_dir = 'DBFTS';
    var $object_name = 'DBFTS';
    var $table_name = 'dbfts_search';
    var $importable = false;
    var $id;
    var $name;
    var $date_entered;
    var $date_modified;
    var $modified_user_id;
    var $modified_by_name;
    var $created_by;
    var $created_by_name;
    var $description;
    var $deleted;
    var $created_by_link;
    var $modified_user_link;
    var $team_id;
    var $team_set_id;
    var $team_count;
    var $team_name;
    var $team_link;
    var $team_count_link;
    var $teams;

    function __construct()
    {
        parent::__construct();
    }

    function performSearch($queryString, $offset = 0, $limit = 20, $options = array()) {
    	$returns = array();
        
        $query = "SELECT id, field_name FROM {$this->table_name} ";
        $this->addVisibilityFrom($query);
        $query .= "WHERE " . $this->db->getFulltextQuery('field_value', array($queryString)) . $this->getModulesWhere($options);
        $this->addVisibilityWhere($query);

        $results = $this->db->limitQuery($query, $offset, $limit);
        
        while($row = $this->db->fetchByAssoc($results)) {
    		$returns[] = BeanFactory::getBean('DBFTS', $row['id']);
    	}

    	return $returns;
    }

    function getModulesWhere($options = array()) {
        // TODO XXX This functionality is the same for all search engines and therefore should be moved to the abstract parent. The search engine implementation should have 1 function which converts this array to a filter or where clause
        $where = '';
        $finalTypes = array();
        if(!empty($options['moduleFilter']))
        {
            if( is_admin($GLOBALS['current_user']) ) {
                $finalTypes = $options['moduleFilter'];
            }
            else
            {
                foreach ($options['moduleFilter'] as $moduleName)
                {
                    $class = $GLOBALS['beanList'][$moduleName];
                    $seed = new $class();
                    // only add the module to the list if it can be viewed
                    if ($seed->ACLAccess('ListView'))
                    {
                        $finalTypes[] = $moduleName;
                    }
                }
            }
            if (!empty($finalTypes))
            {
                $where = " AND parent_type IN ( '" . implode("', '", $finalTypes) . "' ) ";
            }
        }
        return $where;
    }

    function getAllRecords($bean) {
        $returns = array();
        $results = $this->db->query("SELECT id, field_name FROM {$this->table_name} WHERE parent_type = '{$bean->module_dir}' AND parent_id = '{$bean->id}'");
        while($row = $this->db->fetchByAssoc($results)) {
            $returns[$row['field_name']] = BeanFactory::getBean('DBFTS', $row['id']);
        }
        return $returns;
    }

    function deleteAllRecords($bean) {
        if(empty($bean)) {
            $this->db->query("DELETE FROM {$this->table_name}");
        }
        else {
            $this->db->query("DELETE FROM {$this->table_name} WHERE parent_type = '{$bean->module_dir}' AND parent_id = '{$bean->id}'");
        }
        return true;
    }

    function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}

?>
