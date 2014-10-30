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

class TagsRelatedModulesUtilities
{

    /**
     * Returns an array of fields for 'taggable' modules
     *
     * @return array
     */
    public static function getRelatedFields()
    {
        $fields = array();
        foreach ($GLOBALS['beanList'] as $module => $bean) {
            $object = BeanFactory::getObjectName($module);
            $relName = strtolower($module) . "_tags";
            $linkField = VardefManager::getLinkFieldForRelationship($module, $object, $relName);
            if ($linkField) {
                $name = strtolower($module) . '_link';
                $fields[$name] = array(
                    'name' => $name,
                    'vname' => $module,
                    'type' => 'link',
                    'relationship' => $relName,
                    'source' => 'non-db',
                );
            }
        }
        return $fields;
    }
}
