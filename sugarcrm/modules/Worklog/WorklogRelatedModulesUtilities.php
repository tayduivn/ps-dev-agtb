<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * This class generates all the relationship between worklog module and other modules
 * that has worklog_link relationship
 */
class WorklogRelatedModulesUtilities
{
    /**
     * Returns an array of fields for `worklog` field templates
     * Set to let link2 know who is worklog linked to
     */
    public static function getRelatedFields()
    {
        global $dictionary;
        $fields = array();
        foreach ($GLOBALS['beanList'] as $module => $bean) {
            if ($module === "Worklog") {
                continue;
            }

            $object = BeanFactory::getObjectName($module);

            if (empty($dictionary[$object])) {
                VardefManager::loadVardef($module, $object, false, array('ignore_rel_calc_fields' => true));
            }

            // only add when modules supports worklog
            if (isset($dictionary[$object]['fields']['worklog'])) {
                $relName = strtolower($module) . "_worklog";
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
        }
        return $fields;
    }
}
