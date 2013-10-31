<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('data/SugarBeanApiHelper.php');

class LeadsApiHelper extends SugarBeanApiHelper
{
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        foreach ($submittedData as $fieldName => $data) {
            if (isset($bean->field_defs[$fieldName])) {
                $properties = $bean->field_defs[$fieldName];
                $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
                /* Field with name=email is the only field of type=email supported at this time */
                if ($type === 'email') {
                    if ($fieldName !== 'email') {
                        unset($submittedData[$fieldName]);
                    }
                }
            }
        }

        parent::populateFromApi($bean, $submittedData, $options);

        return true;
    }
}

