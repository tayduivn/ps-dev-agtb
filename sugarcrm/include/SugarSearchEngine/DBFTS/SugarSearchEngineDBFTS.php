<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
require_once('include/SugarSearchEngine/SugarSearchEngineHighlighter.php');
require_once('include/SugarSearchEngine/DBFTS/SugarSearchEngineDBFTSResultSet.php');

/**
 * Engine implementation for DBFTSSearch
 */
class SugarSearchEngineDBFTS extends SugarSearchEngineAbstractBase
{
    private $_config = array();
    private $_client = null;

    const DEFAULT_INDEX_TYPE = 'SugarBean';
    const WILDCARD_CHAR = '*';

    public function __construct($params = array()) {}

    /**
     * Either index single bean or add the record to be indexed into _documents for later batch indexing,
     * depending on the $batch parameter
     *
     * @param $bean SugarBean object to be indexed
     * @param $batch boolean whether to do batch index
     */
    public function indexBean($bean, $batch = TRUE)
    {
        $this->createIndexDocument($bean);
    }

    /**
     *
     * Return the 'type' for the index.  By using the bean type we can specify mappings on a per bean basis if we need
     * to in the future.
     *
     * @param $bean
     * @return string
     */
    protected function getIndexType($bean)
    {
        if(!empty($bean->module_dir))
            return $bean->module_dir;
        else
            return self::DEFAULT_INDEX_TYPE;
    }

    /**
     *
     * @param SugarBean $bean
     * @return String owner, or null if no owner found
     */
    protected function getOwnerField($bean)
    {
        // when running full indexing, $bean may be a stdClass and not a SugarBean
        if ($bean instanceof SugarBean)
        {
            return $bean->getOwnerField();
        }
        else if (isset($bean->assigned_user_id))
        {
            return $bean->assigned_user_id;
        }
        else if (isset($bean->created_by))
        {
            return $bean->created_by;
        }
        return null;
    }

    /**
     *
     * @param SugarBean $bean
     * @param $searchFields
     * @return Elastica_Document|null
     */
    public function createIndexDocument($bean, $searchFields = null)
    {
        if($searchFields == null)
            $searchFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($bean);

        if(empty($searchFields))
            return false;

        $dbftsBean = BeanFactory::newBean('DBFTS');
        $current_records = $dbftsBean->getAllRecords($bean);
        
        foreach($searchFields as $fieldName => $fieldDef)
        {
            $dbftsBean = BeanFactory::newBean('DBFTS');
            //All fields have already been formatted to db values at this point so no further processing necessary
            if(!empty($bean->$fieldName) || (isset($current_records[$fieldName]) && $current_records[$fieldName]->field_value != $bean->$fieldName)) {
                $dbftsBean->field_name = $fieldName;
                $dbftsBean->field_value = $bean->$fieldName;
                $dbftsBean->boost = (isset($bean->field_defs[$fieldName]['full_text_search']['boost'])) ? $bean->field_defs[$fieldName]['full_text_search']['boost'] : 1;
                if(isset($current_records[$fieldName]))
                {
                    $dbftsBean->id = $current_records[$fieldName]->id;
                }

                $dbftsBean->parent_type = $bean->module_dir;
                $dbftsBean->parent_id = $bean->id;
                $dbftsBean->team_set_id = $bean->team_set_id;
                $dbftsBean->save();                
            }
        }
        
        $dbftsBean = BeanFactory::newBean('DBFTS');
        if(isset($current_records['assigned_user_id']))
        {
            $dbftsBean->id = $current_records['assigned_user_id']->id;
        }
        $dbftsBean->team_set_id = $bean->team_set_id;
        $dbftsBean->field_name = 'assigned_user_id';
        $dbftsBean->field_value = $bean->assigned_user_id;
        $dbftsBean->parent_type = $bean->module_dir;
        $dbftsBean->parent_id = $bean->id;
        $dbftsBean->save();

    }

    /**
     * This indexes one single bean to Elastic Search engine
     * @param SugarBean $bean
     */
    protected function indexSingleBean($bean)
    {
        $this->createIndexDocument($bean);
    }

    /**
     * (non-PHPdoc)
     * @see SugarSearchEngineInterface::delete()
     */
    public function delete(SugarBean $bean)
    {
        if (self::isSearchEngineDown())
        {
            return false;
        }
        if(empty($bean->id)) {
            return false;
        }

        // create a dbfts bean function that deletes all records for a specific module id
        $dbftsBean = BeanFactory::newBean('DBFTS');
        $dbftsBean->deleteAllRecords($bean);
        return true;
    }

    /**
     * This function returns an array of fields that can be passed to search engine.
     * @param Array $options
     * @return Array array of fields
     */
    protected function getSearchFields($options)
    {
        $fields = array();
        if(!empty($options['moduleFilter'])) {
            foreach ($options['moduleFilter'] as $mod) {
                $fieldDef = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($mod);
                foreach ($fieldDef as $fieldName => $def) {
                    if (!in_array($fieldName, $fields)) {
                        $fields[] = $fieldName;
                    }
                }
            }
        } else {
            $allFieldDef = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
            foreach ($allFieldDef as $fieldDef) {
                foreach ($fieldDef as $fieldName => $def) {
                    if (!in_array($fieldName, $fields)) {
                        $fields[] = $fieldName;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Given fields and options, this function constructs and returns a highlight array that can be passed to
     * search engine.
     * @param SugarBean $bean
     * @param $searchFields
     * @return Elastica_Document|null
     */
    protected function constructHighlightArray($fields, $options)
    {
        if (isset($options['preTags']))
        {
            $preTags = $options['preTags'];
        }
        else
        {
            $preTags = SugarSearchEngineHighlighter::$preTag;
        }

        if (isset($options['postTags']))
        {
            $postTags = $options['postTags'];
        }
        else
        {
            $postTags = SugarSearchEngineHighlighter::$postTag;
        }

        $fieldArray = array();
        $highlightProperties = new stdClass();
        if (isset($options['fragmentSize']))
        {
            $highlightProperties->fragment_size = $options['fragmentSize'] + strlen($preTags) + strlen($postTags);
        }
        else
        {
            $highlightProperties->fragment_size = SugarSearchEngineHighlighter::$fragmentSize + strlen($preTags) + strlen($postTags);
        }

        if (isset($options['fragmentNumber']))
        {
            $highlightProperties->number_of_fragments = $options['fragmentNumber'];
        }
        else
        {
            $highlightProperties->number_of_fragments = SugarSearchEngineHighlighter::$fragmentNumber;
        }

        foreach ($fields as $field)
        {
            $fieldArray[$field] = $highlightProperties;
        }

        $highlighArray = array('fields'=>$fieldArray,
            'order'=>'score',
            'pre_tags'=>array($preTags),
            'post_tags'=>array($postTags));

        return $highlighArray;
    }

    /**
     * This function determines whether we should append wildcard to search string.
     *
     * @param String $queryString
     * @return Boolean
     */
    protected function canAppendWildcard($queryString)
    {
        $queryString = trim(html_entity_decode($queryString, ENT_QUOTES));
        if( substr($queryString, -1) ===  self::WILDCARD_CHAR) {
            return false;
        }

        // for fuzzy search, do not append wildcard
        if( strpos($queryString, '~') !==  false) {
            return false;
        }

        // for range searches, do not append wildcard
        if (preg_match('/\[.*TO.*\]/', $queryString) || preg_match('/{.*TO.*}/', $queryString))
        {
            return false;
        }

        // for group searches, do not append wildcard
        if (preg_match('/\(.*\)/', $queryString))
        {
            return false;
        }

        // when using double quotes, do not append wildcard
        if( strpos($queryString, '"') !==  false) {
            return false;
        }

        return true;
    }


    /**
     * This function constructs and returns type term filter for elasticsearch query.
     *
     * @return where
     */
    protected function getTypeTermFilter($modules, &$module_where)
    {
        $module_where = array();
        foreach($modules AS $module) {
            $module_where[] = "parent_type = '{$module}'";
        }
        return $module_where;
    }

    /**
     * This function constructs and returns owner term filter for elasticsearch query.
     *
     * @return where
     */
    protected function getOwnerTermFilter()
    {
        return array("fieldName='assigned_user_id' AND fieldValue='{$GLOBALS['current_user']->id}");
    }

    /**
     * @param $queryString
     * @param int $offset
     * @param int $limit
     * @return array of beans
     */
    public function search($queryString, $offset = 0, $limit = 20, $options = array())
    {
        if (self::isSearchEngineDown())
        {
            return null;
        }

        if($this->canAppendWildcard($queryString)) {
            $queryString .= self::WILDCARD_CHAR;
        }

        $GLOBALS['log']->info("Going to search with query $queryString");


        $dbftsBean = BeanFactory::newBean('DBFTS');

        $results = $dbftsBean->performSearch($queryString, $offset, $limit, $options);

        $return = array();

        foreach($results AS $result) {
            $bean = BeanFactory::getBean($result->parent_type, $result->parent_id);
            $return[$bean->id] = new SugarSearchEngineDBFTSResult($bean);
        }
        $return = array_values($return);
        $resultset = new SugarSearchEngineDBFTSResultSet($return);
        return $resultset;
    }
    
    public function getServerStatus() {
        return array('valid' => true, 'status' => "DBFTS is currently online and ready.");
    }
    
    public function bulkInsert(array $docs) {}

    public function createIndex($recreate = false) {}

}
