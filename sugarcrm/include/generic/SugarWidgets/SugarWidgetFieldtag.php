<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Report widget field that handles specifics of Tag field types
 */
class SugarWidgetFieldTag extends SugarWidgetFieldVarchar {
    /**
     * Handles formatting of a Tag field for rendering on report list views
     * 
     * @param array $layout_def The defs if the field from the report
     * @return string
     */
    function displayList(&$layout_def)
    {
        if(!empty($layout_def['column_key'])){
            $field_def = $this->reporter->all_fields[$layout_def['column_key']];
        }else if(!empty($layout_def['fields'])){
            $field_def = $layout_def['fields'];
        }
        $cell = $this->displayListPlain($layout_def);
        
        // $cell should be ^..^,^..^
        // No, that isn't an emotibatman
        $data = implode(', ', explode('^,^', trim($cell, '^')));
        return $data;
    }

    /**
     * Handles WHERE query building for CONTAINS requests
     * 
     * @param array $layout_def The defs if the field from the report
     * @return string
     */
    function queryFilterContains(&$layout_def)
    {
        $matches = explode(',', $layout_def['input_name0']);
        $q = "";
        foreach ($matches as $match) {
            $match = trim($match);
            $q .= " " . $this->_get_column_select($layout_def) . " LIKE '%" .$GLOBALS['db']->quote($match)."%' OR";
        }

        return rtrim($q, " OR");
    }

    /**
     * Handles WHERE query building for NOT CONTAINS requests
     * 
     * @param array $layout_def The defs if the field from the report
     * @return string
     */
    function queryFilterNot_Contains(&$layout_def)
    {
        $matches = explode(',', $layout_def['input_name0']);
        $q = "";
        foreach ($matches as $match) {
            $match = trim($match);
            $q .= " " . $this->_get_column_select($layout_def) . " NOT LIKE '%" .$GLOBALS['db']->quote($match)."%' AND";
        }

        return rtrim($q, " AND");
    }
}
