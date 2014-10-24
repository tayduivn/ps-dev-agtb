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

        $lowercaseValues = array();
        foreach ($params[$field] as $key => &$record) {
            // First create tag bean if it needs to be created
            $this->getTagBean($record);

            // Format tag to look more like a multienum field
            $record = $record['name'];

            // Make a lowercase version for storage and retrieval
            $lowercaseValues[] = strtolower($record);
        }

        // Make the lowercase version of the tag and save that to the bean
        if (isset($bean->field_defs[$field . '_lower']) && !empty($lowercaseValues)) {
            $bean->{$field . '_lower'} = implode(' ', $lowercaseValues);
        }

        // Then save tags as a field on current bean
        return $this->save($bean, $params, $field, $properties);
    }

    protected function getTagBean($record)
    {
        // We'll need this no matter what
        $tagBean = BeanFactory::getBean('Tags');

        if (!empty($record['id'])) {
            if ($tagBean->retrieve($record['id'])) {
                return $tagBean;
            }
        }

        // See if this tag exists already. If it does send back the bean for it
        $q = $this->getSugarQuery();
        $q->select(array('id', 'name'));
        $q->from($tagBean);
        // Get the tag from the lowercase version of the name
        $q->where()->equals('name_lower', strtolower($record['name']));
        $result = $q->execute();

        // If there is a result for this tag name, send back the bean for it
        if (!empty($result[0]['id'])) {
            if ($tagBean->retrieve($result[0]['id'])) {
                return $tagBean;
            }
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
                $tag = $tag['name'];
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     * Override multienum to make a value list of value1,value2,value3 from
     * ^value1^,^value2^,^value3^
     */
    public function exportSanitize($value, $vardef, $focus, $row=array())
    {
        $values = unencodeMultienum($value);
        return implode(',', $values);
    }

    /**
     * Gets the tags for a bean as an array of values
     *
     * @param SugarBean $bean The SugarBean that you are getting a value of
     * @param string $field The field to get a normal value from
     * @return Array
     */
    public function getTagValues(SugarBean $bean, $field)
    {
        return $this->getNormalizedFieldValues($bean, $field);
    }

    /**
     * Reads a string of input from an import process and gets the tag values from
     * that string. The import string should look like Value1,Value2,Value3
     *
     * @param string $value The import row of data
     * @return array
     */
    public function getTagValuesFromImport($value)
    {
        if (empty($value)) {
            return array();
        }

        if (is_array($value)) {
            return $value;
        }

        return explode(',', trim($value));
    }
}
