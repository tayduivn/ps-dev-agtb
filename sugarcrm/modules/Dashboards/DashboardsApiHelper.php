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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'data/SugarBeanApiHelper.php';

class DashboardsApiHelper extends SugarBeanApiHelper
{
    /**
     * 'view' is deprecated because it's reserved db word.
     * Some old API (before 7.2.0) can use 'view'.
     * Because of that API will use 'view' as 'view_name' if 'view_name' isn't present.
     *
     * @param SugarBean $bean
     * @param array     $submittedData
     * @param array     $options
     *
     * @return array
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        if (isset($submittedData['view']) && !isset($submittedData['view_name'])) {
            $submittedData['view_name'] = $submittedData['view'];
        }
        return parent::populateFromApi($bean, $submittedData, $options);
    }

    /**
     * 'view' is deprecated because it's reserved db word.
     * Some old API (before 7.2.0) can use 'view'.
     * Because of that API will return 'view' with the same value as 'view_name'.
     *
     * @param SugarBean $bean
     * @param array     $fieldList
     * @param array     $options
     *
     * @return array
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        $data = parent::formatForApi($bean, $fieldList, $options);
        if (isset($data['view_name'])) {
            $data['view'] = $data['view_name'];
        }
        return $data;
    }
}
