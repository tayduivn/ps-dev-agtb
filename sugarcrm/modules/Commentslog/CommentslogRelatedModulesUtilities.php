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
 * This class generates all the relationship between commentslog module and other modules
 * that has commentsLog_link relationship
 */
class CommentslogRelatedModulesUtilities
{
    /**
     * Returns an array of fields for `commentslog` field templates
     * Set to let link2 know who is commentslog linked to
     */
    public static function getRelatedFields()
    {
        global $dictionary;
        $fields = array();
        foreach ($GLOBALS['beanList'] as $module => $bean) {
            if ($module === "Commentslog") {
                continue;
            }

            $object = BeanFactory::getObjectName($module);

            if (empty($dictionary[$object])) {
                VardefManager::loadVardef($module, $object, false, array('ignore_rel_calc_fields' => true));
            }

            // only add when modules supports commentslog
            if (isset($dictionary[$object]['fields']['commentslog'])) {
                $relName = strtolower($module) . "_commentslog";
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
