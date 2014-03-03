<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
********************************************************************************/

require_once 'include/SugarFields/Fields/Base/SugarFieldBase.php';

/**
 *
 * Related collection field
 *
 * This field type can be used on record views to show related records for
 * the given bean context. Essentially a related collection field acts the
 * same as a subpanel, except that the records are shown directly in the
 * record view itself.
 *
 * There are already certain implementation which look alike, for example
 * email addresses and team sets. This sugar field type uses a generic
 * approach from which can be extended.
 *
 * Available vardef settings:
 *
 * array(
 * 	'type' => 'relatedcollection'
 *   'link' => pointer to a vardef link field defining the relationship
 *   	Required parameter without a default
 * 	'collection_fields' => array of fields to return from related object
 * 		Optional, defaults to id and name field
 * 	'collection_limit' => maximum amount of related records to return
 * 		Optional, defaults to unlimited
 *  'collection_create' =>  ability to create new objects while linking
 *  	Optional, defaults to false
 * )
 *
 */
class SugarFieldRelatecollection extends SugarFieldBase
{
    /**
     *
     * Base fields for collection
     * @var array
     */
    protected static $baseFields = array(
        'id',
        'name',
    );

    /**
     *
     * Base collection limit
     * @var integer
     */
    protected static $baseLimit = -1;

    /**
     *
     * {@inheritdoc}
     */
    public function apiFormatField(array &$data, SugarBean $bean, array $args, $fieldName, $properties)
    {
        list ($relName, $fields, $limit) = $this->parseProperties($properties);
        $records = $this->getLinkedRecords($bean, $relName, $fields, $limit);
        $data[$fieldName] = array_values($records);
    }

    /**
     *
     * {@inheritdoc}
     */
    public function apiSave($bean, $params, $field, $properties)
    {
        if (empty($params[$field]) || !is_array($params[$field])) {
            return;
        }

        // retrieve current linked objects
        list ($relName, $fields, $limit, $create) = $this->parseProperties($properties);
        $currentList = $this->getLinkedRecords($bean, $relName, $fields, $limit);

        /*
         * We do not require the client to send back the full list of related
         * items. Only explicit additions/removals are required. Already
         * present links are maintained if not explicitly defined during save.
         */
        foreach ($params[$field] as $record) {

            // validate required fields
            if (!$this->validateRequiredFields($record)) {
                continue;
            }

            // handle (new) related records
            if (empty($record['removed'])) {

                // create new bean first if supported when no id is provided
                if ($record['id'] === false && $create) {
                    $new = $this->createNewBeanBeforeLink($bean, $relName, $record);
                    if (!empty($new->id)) {
                        $bean->$relName->add($new);
                    }
                }

                // add new link if it doesn't exist yet
                if ($record['id'] && !isset($currentList[$record['id']])) {
                    $bean->$relName->add($record['id']);
                }

            // handle related records flagged for removal
            } elseif (!empty($record['removed']) && !empty($record['id'])) {

                // just remove the link, Link2 will take care of the checks
                $bean->$relName->delete($bean->id, $record['id']);
            }
        }
    }

    /**
     *
     * Create a new bean before linking it to the parent
     * @param SugarBean $parent
     * @param string $relName Relationship name
     * @param array $record Data to use to create related bean
     * @return SugarBean
     */
    protected function createNewBeanBeforeLink(SugarBean $parent, $relName, array $record)
    {
        $relSeed = $this->getRelatedSeedBean($parent, $relName);
        $new = BeanFactory::getBean($relSeed->module_name);
        $new->fromArray($record);
        $new->save();
        return $new;
    }

    /**
     *
     * Check if required fields are present for given record (base fields).
     * @param array $record
     * @return boolean
     */
    protected function validateRequiredFields(array $record)
    {
        foreach (self::$baseFields as $field) {
            if (!isset($record[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * Return linked object data for given bean/relationship.
     * @param SugarBean $parent
     * @param string    $relName
     * @param array     $fields
     * @param integer   $limit
     * @return array
     */
    protected function getLinkedRecords(SugarBean $parent, $relName, array $fields, $limit)
    {
        if (! $relSeed = $this->getRelatedSeedBean($parent, $relName)) {
            return array();
        }

        // base query object for related module
        $sq = $this->getSugarQuery();
        $sq->select($fields);
        $sq->from($relSeed);

        if ($limit > 0) {
            $sq->limit($limit);
        }

        // join against parent module
        $sq->joinSubpanel($parent, $relName);

        $result = array();
        foreach ($sq->execute('array') as $record) {
            $result[$record['id']] = $record;
        }
        return $result;
    }

    /**
     *
     * Parse field properties, return defaults if not set.
     * @param array $properties
     * @return array
     */
    protected function parseProperties(array $properties)
    {
        // link is required
        $link = empty($properties['link']) ? false : $properties['link'];

        // field list
        $fields = self::$baseFields;
        if (!empty($properties['collection_fields']) && is_array($properties['collection_fields'])) {
            $fields = array_unique(array_merge(self::$baseFields, $properties['collection_fields']));
        }

        // maximum related records
        $limit = (int) empty($properties['collection_limit']) ? self::$baseLimit : $properties['collection_limit'];

        // create linked object (disabled by default)
        $create = !empty($properties['collection_create']) ?: false;

        return array($link, $fields, $limit, $create);
    }

    /**
     *
     * Return a SugarBean for the other end of a given bean/relationship.
     * @param SugarBean $bean
     * @param string     $rel Link name
     * @return mixed (SugarBean|null)
     */
    protected function getRelatedSeedBean(SugarBean $bean, $rel)
    {
        if ($bean->load_relationship($rel)) {
            return BeanFactory::getBean($bean->$rel->getRelatedModuleName());
        }
    }

    /**
     *
     * Return a new SugarQuery object.
     * @return SugarQuery
     */
    protected function getSugarQuery()
    {
        return new SugarQuery();
    }
}
