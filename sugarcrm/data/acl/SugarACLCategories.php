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

/**
 * Class SugarACLCategories
 * Additional ACL for Categories.
 */
class SugarACLCategories extends SugarACLStatic
{
    protected $aclModule = '';

    public function __construct($aclOptions)
    {
        if (empty($aclOptions['aclModule'])) {
            throw new SugarException('No sense to use SugarACLCategories strategy without aclModule definition.');
        }
        $this->aclModule = $aclOptions['aclModule'];
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess($module, $view, $context)
    {
        if ($view == "field") {
            return true;
        }
        return parent::checkAccess($this->aclModule, $view, $context);
    }

    /**
     * {@inheritDoc}
     *
     * Check access for the list of fields
     * @param string $module
     * @param array $field_list key=>value list of fields
     * @param string $action Action to check
     * @param array $context
     * @return array[boolean] Access for each field, array() means all allowed
     */
    public function checkFieldList($module, $field_list, $action, $context)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     *
     * Get access for the list of fields
     * @param string $module
     * @param array $field_list key=>value list of fields
     * @param array $context
     * @return array[int] Access for each field, array() means all allowed
     */
    public function getFieldListAccess($module, $field_list, $context)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserAccess($module, $access_list, $context)
    {
        return parent::getUserAccess($this->aclModule, $access_list, $context);
    }
}
