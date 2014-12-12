<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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

    /**
     * @inheritDoc
     */
    public function normalizeDefaultValue($value) {
        // Treat field as an import list to make tags from string
        $tags = $this->getTagValuesFromImport($value);
        // Trim white space from each tag so users who type in 'tag1, tag2' won't end up with the tag: ' tag2'
        $tags = array_map('trim', $tags);
        // Sort tags in alphabetical order
        sort($tags);

        // Format tags to what the front end will expect
        $return = array();
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $return[] = array('name' => "$tag");
            }
        }
        return $return;
    }
}
