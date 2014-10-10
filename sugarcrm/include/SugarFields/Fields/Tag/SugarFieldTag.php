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
require_once 'include/SugarFields/Fields/Multienum/SugarFieldMultienum.php';

class SugarFieldTag extends SugarFieldMultienum
{
    /**
     * Override of parent apiSave to force the custom save to be run from API
     * @param SugarBean $bean
     * @param array     $params
     * @param string    $field
     * @param array     $properties
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties) {
        if (empty($params[$field]) || !is_array($params[$field])) {
            return;
        }

        foreach ($params[$field] as $key => &$record) {
            // First create tag bean if it needs to be created
            $this->getTagBean($record);

            // If there is no removed record request...
            if (empty($record['removed'])) {
                $record = $record['name'];
            } else {
                unset($params[$field][$key]);
            }
        }

        // Then save tags as a field on current bean
        return $this->save($bean, $params, $field, $properties);
    }

    protected function getTagBean($record)
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
     *
     * Return a new SugarQuery object.
     * @return SugarQuery
     */
    protected function getSugarQuery()
    {
        return new SugarQuery();
    }

    /**
     * {@inheritDoc}
     */
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties) {
        if ($bean->$fieldName) {
            $tags = $this->getNormalizedFieldValues($bean, $fieldName);
            foreach ($tags as &$tag) {
                $tag = array('name' => "$tag");
            }
            // Sort tags in alphabetical order before returning them
            sort($tags);
            $data[$fieldName] = $tags;
        } else {
            $data[$fieldName] = '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fixForFilter(&$value, $fieldName, SugarBean $bean, SugarQuery $q, SugarQuery_Builder_Where $where, $op)
    {
        if (is_array($value)) {
            foreach($value as $key => &$tag) {
                if (empty($tag['removed'])) {
                    $tag = $tag['name'];
                } else {
                    unset($value[$key]);
                }
            }
        }
        return true;
    }
}
