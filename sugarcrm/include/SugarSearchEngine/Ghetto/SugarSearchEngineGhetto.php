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

/**
 * Engine implementation for GhettoSearch
 */
class SugarSearchEngineGhetto extends SugarSearchEngineAbstractBase
{
    private $_config = array();
    private $_client = null;
    private $_indexName = "";

    const DEFAULT_INDEX_TYPE = 'SugarBean';
    const WILDCARD_CHAR = '%';

    private $_indexType = 'SugarBean';

    public function __construct($params = array())
    {
        $this->_config = $params;
        $this->_indexName = strtolower($GLOBALS['sugar_config']['unique_key']);

        //Ghetto client uses own auto-load schema similar to ZF.
        spl_autoload_register(array($this, 'loader'));
        if (empty($this->_config['timeout']))
        {
            $this->_config['timeout'] = 15;
        }
    }

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

        $ghettoBean = BeanFactory::newBean('GhettoSearch');
        $current_records = $ghettoBean->getAllRecords($bean);

        foreach($searchFields as $fieldName => $fieldDef)
        {
            //TODO: CHANGE ME
            $ghettoBean = BeanFactory::newBean('GhettoSearch');                            
            //All fields have already been formatted to db values at this point so no further processing necessary
            if(!empty($bean->$fieldName)) {
                $ghettoBean->fieldName = $fieldName;
                $gettoBean->fieldValue = $bean->$fieldName;
                $ghettoBean->boostValue = $bean->fieldDefs[$fieldName]->boostValue;
            }
            if(isset($current_records[$fieldName]))
            {
                $ghettoBean->id = $current_records[$fieldName]->id;
            }

            $ghettoBean->module = $bean->module_dir;
            $ghettoBean->module_id = $bean->id;
            $ghettoBean->save();
        }
        
        $ghettoBean = BeanFactory::newBean('Ghetto');
        if($current_records['assigned_user_id'])
        {
            $ghettoBean->id = $current_records['assigned_user_id']->id;
        }
        $ghettoBean->fieldName = 'assigned_user_id';
        $ghettoBean->fieldValue = $bean->assigned_user_id;
        $ghettoBean->module = $bean->module_dir;
        $ghettoBean->module_id = $bean->id;
        $ghettoBean->save();

        $ghettoBean = BeanFactory::newBean('Ghetto');
        if($current_records['team_set_id'])
        {
            $ghettoBean->id = $current_records['assigned_user_id']->id;
        }        
        $ghettoBean->fieldName = 'team_set_id';
        $ghettoBean->fieldValue = $bean->team_set_id;
        $ghettoBean->module = $bean->module_dir;
        $ghettoBean->module_id = $bean->id;
        $ghettoBean->save();

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

        //TODO: add deleteAllRecords
        // create a ghetto bean function that deletes all records for a specific module id
        $ghettoBean = BeanFactory::newBean('GhettoSearch');
        $ghettoBean->deleteAllRecords($bean);
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
     * This function constructs and returns team filter for elasticsearch query.
     *
     * @return where
     */
    protected function constructTeamFilter()
    {
        $teamIDS = TeamSet::getTeamSetIdsForUser($GLOBALS['current_user']->id);

        //TODO: Determine why term filters aren't working with the hyphen present.
        //Term filters dont' work for terms with '-' present so we need to clean
        $teamIDS = array_map(array($this,'cleanTeamSetID'), $teamIDS);

        $termFilter = new Ghetto_Filter_Terms('team_set_id', $teamIDS);

        return $termFilter;
    }

    /**
     * This function constructs and returns type term filter for elasticsearch query.
     *
     * @return where
     */
    protected function getTypeTermFilter($module)
    {
        $typeTermFilter = new Ghetto_Filter_Term();
        $typeTermFilter->setTerm('_type', $module);

        return $typeTermFilter;
    }

    /**
     * This function constructs and returns owner term filter for elasticsearch query.
     *
     * @return where
     */
    protected function getOwnerTermFilter()
    {
        $ownerTermFilter = new Ghetto_Filter_Term();
        $ownerTermFilter->setTerm('doc_owner', $GLOBALS['current_user']->id);

        return $ownerTermFilter;
    }

    /**
     * This function constructs and returns module level filter for elasticsearch query.
     *
     * @return where
     */
    protected function constructModuleLevelFilter($module)
    {
        $requireOwner = ACLController::requireOwner($module, 'list');

        $class = $GLOBALS['beanList'][$module];
        $seed = new $class();
        $hasAdminAccess = $GLOBALS['current_user']->isAdminForModule($seed->getACLCategory());

        if ($hasAdminAccess)
        {
            // user has admin access for this module, skip team filter
            if ($requireOwner)
            {
                // need to be document owner to view
                $moduleFilter = new Ghetto_Filter_And();

                // type term filter
                $typeTermFilter = $this->getTypeTermFilter($module);
                $moduleFilter->addFilter($typeTermFilter);

                // owner term filter
                $ownerTermFilter = $this->getOwnerTermFilter();
                $moduleFilter->addFilter($ownerTermFilter);
            }
            else
            {
                // do not need to be document owner to view
                // a single type term filter is all we need
                $moduleFilter = $this->getTypeTermFilter($module);
            }
        }
        else
        {
            // user does not have admin access, need team filter
            $moduleFilter = new Ghetto_Filter_And();

            // team filter
            $teamFilter = $this->constructTeamFilter();
            $moduleFilter->addFilter($teamFilter);

            // type term filter
            $typeTermFilter = $this->getTypeTermFilter($module);
            $moduleFilter->addFilter($typeTermFilter);

            if ($requireOwner)
            {
                // need to be document owner to view, owner term filter
                $ownerTermFilter = $this->getOwnerTermFilter();
                $moduleFilter->addFilter($ownerTermFilter);
            }
        }
        return $moduleFilter;
    }

    /**
     * This function constructs and returns main filter for elasticsearch query.
     *
     * @return where
     */
    protected function constructMainFilter($finalTypes)
   {
        $mainFilter = new Ghetto_Filter_Or();
        foreach ($finalTypes as $module)
        {
            $moduleFilter = $this->constructModuleLevelFilter($module);
            
            // if we want myitems add more to the module filter
            if(isset($options['my_items']) && $options['my_items'] !== false) {
                $moduleFilter = $this->myItemsSearch($moduleFilter);
            }
            if(isset($options['filter']) && $options['filter']['type'] == 'range') {
                $moduleFilter = $this->constructRangeFilter($moduleFilter, $options['filter']);
            }
            //BEGIN SUGARCRM flav=pro ONLY
            
            // we only want JUST favorites if the option is 2
            // if the option is 1 that means we want all including favorites,
            // which in FTS is a normal search parameter
            if(isset($options['favorites']) && $options['favorites'] == 2) {
                $moduleFilter = $this->constructMyFavoritesFilter($moduleFilter);
            }

            //END SUGARCRM flav=pro ONLY

            $mainFilter->addFilter($moduleFilter);

        }

        return $mainFilter;
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

        $appendWildcard = false;
        if( !empty($options['append_wildcard']) && $this->canAppendWildcard($queryString) )
        {
            $appendWildcard = true;
        }
        $queryString = sql_like_string($queryString, self::WILDCARD_CHAR, self::WILDCARD_CHAR, $appendWildcard);

        $GLOBALS['log']->info("Going to search with query $queryString");
        $results = null;

        $ghettoBean = BeanFactory::newBean();


        //TODO: add performSearch
        $results = $ghettoBean->performSearch($queryString, $offset, $limit, $options);

        $return = array();
        foreach($results AS $ghettoLicious) {
            $return[] = BeanFactory::getBean($ghettoLicious->module, $ghettoLicious->module_id);
        }
        return $return;
    }

 
}
