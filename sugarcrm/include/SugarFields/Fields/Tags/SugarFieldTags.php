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

class SugarFieldTags extends SugarFieldRelatecollection
{
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
     * Fix a value(s) for a Filter statement
     * @param $value - the value that needs fixing
     * @param $fieldName - the field we are fixing
     * @param SugarBean $bean - the Bean
     * @param SugarQuery $q - the Query
     * @param SugarQuery_Builder_Where $where - the Where statement
     * @param $op - the filter operand
     * @return bool - true if everything can pass as normal, false if new filters needed to be added to override the existing $op
     */
    public function fixForFilter(&$value, $fieldName, SugarBean $bean, SugarQuery $q, SugarQuery_Builder_Where $where, $op)
    {
        if (is_array($value)) {
            foreach($value as $i => $tag) {
                if (empty($tag['removed'])) {
                    $value[$i] = $tag['name'];
                } else {
                    unset($value[$i]);
                }
            }
        }
        if (empty($value)) {
            return false;
        }
        $tableAlias = $q->getJoinTableAlias($fieldName);
        $field = "$tableAlias.name";

        switch ($op) {
            case '$in':
                $where->in($field, $value);
                break;
            case '$not_in':
                $where->notIn($field, $value);
                break;
        }
        return false;
    }
}
