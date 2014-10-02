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
require_once 'include/SugarFields/Fields/Relatecollection/SugarFieldRelatecollection.php';

class SugarFieldTag extends SugarFieldRelatecollection
{
    /**
     *
     * Base fields for collection
     * @var array
     */
    protected $baseFields = array(
        'name',
    );

    /**
     * {inheritdoc}
     */
    protected function parseProperties(array $properties)
    {
        // force specific tag properties
        $properties['collection_create'] = true;
        $properties['collection_fields'] = array('id', 'name');
        return parent::parseProperties($properties);
    }

    /**
     * {inheritdoc}
     */
    protected function getRelatedRecord($bean, $relName, $record)
    {
        // We'll need this no matter what
        $tagBean = BeanFactory::getBean('Tags');

        if (!empty($record['id'])) {
            $tagBean->retrieve($record['id']);
            return $tagBean;
        }

        // See if this tag exists already. If it does send back the bean for it
        $q = $this->getSugarQuery();
        $q->select(array('id', 'name'));
        $q->from($tagBean);
        $q->where()->equals('name', $record['name']);
        $result = $q->execute();

        // If there is a result for this tag name, send back the bean for it
        if (!empty($result[0]['id'])) {
            $tagBean->retrieve($result[0]['id']);
            return $tagBean;
        }

        // Create a new record and send back THAT bean
        $tagBean->fromArray(array('name' => $record['name']));
        $tagBean->save();
        return $tagBean;
    }

    /**
     * {inheritdoc}
     */
    public function fixForFilter(&$value, $fieldName, SugarBean $bean, SugarQuery $q, SugarQuery_Builder_Where $where, $op)
    {
        if (is_array($value)) {
            foreach($value as &$tag) {
                $tag = $tag['name'];
            }
        }
        return true;
    }

    /**
     * {inheritdoc}
     */
    protected function getOrderBy() {
        return array(
            'fieldName' => 'name',
            'order' => 'ASC'
        );
    }
}
