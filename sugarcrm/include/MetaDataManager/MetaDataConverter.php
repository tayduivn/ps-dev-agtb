<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Assists in backporting 6.6 Metadata formats to legacy style in order to
 * maintain backward compatibility with old clients consuming the V3 and V4 apis.
 */
class MetaDataConverter
{
    /**
     * An instantiated object of MetaDataConverter type
     *
     * @var MetaDataConverter
     */
    protected static $converter = null;

    /**
     * Converts edit and detail view defs that contain fieldsets to a compatible
     * defs that does not contain fieldsets. In essence, it splits up any fieldsets
     * and moves them out of their grouping into individual fields within the panel.
     *
     * This method assumes that the defs have already been converted to a legacy
     * format.
     *
     * @param array $defs
     * @return array
     */
    public static function fromGridFieldsets(array $defs)
    {
        if (isset($defs['panels']) && is_array($defs['panels'])) {
            $newpanels = array();
            $offset = 0;
            foreach ($defs['panels'] as $row) {
                if (is_array($row[0]) && isset($row[0]['type'])
                    && $row[0]['type'] == 'fieldset' && isset($row[0]['related_fields'])
                ) {
                    // Fieldset.... convert
                    foreach ($row[0]['related_fields'] as $fName) {
                        $newpanels[$offset] = array($fName);
                        $offset++;
                    }
                } else {
                    // do nothing
                    $newpanels[$offset] = $row;
                    $offset++;
                }
            }

            $defs['panels'] = $newpanels;
        }

        return $defs;
    }

    /**
     * Static entry point, will instantiate an object of itself to run the process.
     * Will convert $defs to legacy format $viewtype if there is a converter for
     * it, otherwise will return the defs as-is with no modification.
     *
     * @static
     * @param string $viewtype One of list|edit|detail
     * @param array $defs The defs to convert
     * @return array Converted defs if there is a converter, else the passed in defs
     */
    public static function toLegacy($viewtype, $defs)
    {
        if (null === self::$converter) {
            self::$converter = new self;
        }

        $method = 'toLegacy' . ucfirst(strtolower($viewtype));
        if (method_exists(self::$converter, $method)) {
            return self::$converter->$method($defs);
        }

        return $defs;
    }

    /**
     * Takes in a 6.6+ version of mobile|portal|sidecar list view metadata and
     * converts it to pre-6.6 format for legacy clients. The formats of the defs
     * are pretty dissimilar so the steps are going to be:
     *  - Take in all defs
     *  - Clip everything but the fields portion of the panels section of the defs
     *  - Modify the fields array to be keyed on UPPERCASE field name
     *
     * @param array $defs Field defs to convert
     * @return array
     */
    public function toLegacyList(array $defs)
    {
        $return = array();

        // Check our panels first
        if (isset($defs['panels']) && is_array($defs['panels'])) {
            foreach ($defs['panels'] as $panels) {
                // Handle fields if there are any (there should be)
                if (isset($panels['fields']) && is_array($panels['fields'])) {
                    // Logic here is simple... pull the name index value out, UPPERCASE it and
                    // set that as the new index name
                    foreach ($panels['fields'] as $field) {
                        if (isset($field['name'])) {
                            $name = strtoupper($field['name']);
                            unset($field['name']);
                            $return[$name] = $field;
                        }
                    }
                }
            }
        }


        return $return;
    }

    /**
     * Takes a Sidecar Subpanel view def and returns a BWC compatibile Subpanel view def
     *
     * @param array $oldDefs Field defs to convert
     * @param string $moduleName, the module we are converting
     * @return array BWC defs
     */
    public function toLegacySubpanelsViewDefs(array $defs, $moduleName)
    {
        if (!isset($defs['panels'])) {
            return array();
        }

        $oldDefs = array();

        // for BWC, we need to have some top buttons.  Sidecar doesn't have buttons in the def
        $oldDefs['top_buttons'] = array(
            array(
                'widget_class' => 'SubPanelTopCreateButton'
            ),
            array(
                'widget_class' => 'SubPanelTopSelectButton',
                'popup_module' => $moduleName,
            ),
        );

        $oldDefs['list_fields'] = $this->toLegacyList($defs);
        return $oldDefs;
    }

    /**
     * Convert legacy subpanels view defs to sidecar subpanel view defs
     * @param array $defs
     * @return array
     */
    public function fromLegacySubpanelsViewDefs(array $defs)
    {
        if (!isset($defs['list_fields'])) {
            throw new \RuntimeException("Subpanel is defined without fields");
        }

        $viewdefs = array('panels' => array());

        $viewdefs['panels'][0]['name'] = 'panel_header';
        $viewdefs['panels'][0]['label'] = 'LBL_PANEL_1';

        $viewdefs['panels'][0]['fields'] = array();

        foreach ($defs['list_fields'] as $fieldName => $details) {
            if (isset($details['vname'])) {
                $details['label'] = $details['vname'];
            }
            // disregard buttons
            if ((isset($details['label']) && stripos($details['label'], 'button') !== false) ||
                stripos($fieldName, 'button') !== false
            ) {
                continue;
            }

            if (isset($details['usage'])) {
                continue;
            }

            if (!isset($details['default'])) {
                $details['default'] = true;
            }

            if (!isset($details['enabled'])) {
                $details['enabled'] = true;
            }

            $details['name'] = $fieldName;
            $viewdefs['panels'][0]['fields'][] = $this->fromLegacySubpanelField($details);
        }
        return $viewdefs;
    }

    /**
     * Convert a single field from the old subpanel fielddef
     * to the new sidecar def.
     *
     * This will return an array that contains any of the following:
     * label - the field label, will use vname if label doesn't exist
     * width - the width of the field
     * type - the field type [varchar, etc]
     * target module - for link fields the target module
     * target record key - for link fields the target key for the target_module
     *
     * @param array $details
     * @return array
     */
    public function fromLegacySubpanelField(array $fieldDefs)
    {
        static $fieldMap = array(
            'name' => true,
            'label' => true,
            'type' => true,
            'target_module' => true,
            'target_record_key' => true,
            'default' => true,
            'enabled' => true,
        );

        return array_intersect_key($fieldDefs, $fieldMap);
    }

    /**
     * @param array $layoutDefs
     * @param SugarBean $bean
     * @return array legacy LayoutDef
     */
    public function toLegacySubpanelLayoutDefs(array $layoutDefs, SugarBean $bean)
    {
        $return = array();

        foreach ($layoutDefs as $order => $def) {
            // no link can't move on
            if (empty($def['context']['link'])) {
                continue;
            }
            $link = new Link2($def['context']['link'], $bean);
            $linkModule = $link->getRelatedModuleName();
            // if we don't have a label at least set the module name as the label
            // similar to configure shortcut bar
            $label = isset($def['label']) ? $def['label'] : translate($linkModule);
            $return[$def['context']['link']] = array(
                'order' => $order,
                'module' => $bean->module_dir,
                'subpanel_name' => 'default',
                'sort_order' => 'asc',
                'sort_by' => 'id',
                'title_key' => $label,
                'get_subpanel_data' => $def['context']['link'],
                'top_buttons' => array(
                    array(
                        'widget_class' => 'SubPanelTopButtonQuickCreate',
                    ),
                    array(
                        'widget_class' => 'SubPanelTopSelectButton',
                        'mode' => 'MultiSelect',
                    ),
                ),
            );
        }
        return array('subpanel_setup' => $return);
    }

    /**
     * Simple accessor into the grid legacy converter
     *
     * @param array $defs Field defs to convert
     * @return array
     */
    public function toLegacyEdit(array $defs)
    {
        return $this->toLegacyGrid($defs);
    }

    /**
     * Simple accessor into the grid legacy converter
     *
     * @param array $defs Field defs to convert
     * @return array
     */
    public function toLegacyDetail(array $defs)
    {
        return $this->toLegacyGrid($defs);
    }

    /**
     * Takes in a 6.6+ version of mobile|portal|sidecar edit|detail view metadata and
     * converts it to pre-6.6 format for legacy clients.
     *
     * NOTE: This will only work for layouts that have only one field per row. For
     * the 6.6 upgrade that is sufficient since we were only converting portal
     * and mobile viewdefs. As is, this method will NOT convert grid layout view
     * defs that have more than one field per row.
     *
     * @param array $defs
     * @return array
     */
    protected function toLegacyGrid(array $defs)
    {
        // Check our panels first
        if (isset($defs['panels']) && is_array($defs['panels'])) {
            // For our new panels
            $newpanels = array();
            foreach ($defs['panels'] as $panels) {
                // Handle fields if there are any (there should be)
                if (isset($panels['fields']) && is_array($panels['fields'])) {
                    // Logic is fairly straight forward... take each member of 
                    // the fields array and make it an array of its own
                    foreach ($panels['fields'] as $field) {
                        $newpanels[] = array($field);
                    }
                }
            }

            unset($defs['panels']);
            $defs['panels'] = $newpanels;
        }

        return $defs;
    }

    /**
     * Convert a legacy subpanel name to the new sidecar name
     * Examples:
     * ForAccounts becomes subpanel-for-accounts
     * default becomes subpanel-list
     *
     * @param string $subpanelName the legacy subpanel
     * @return string the new subpanel name
     */
    public function fromLegacySubpanelName($subpanelName)
    {
        $newName = ($subpanelName === 'default') ? 'list' : str_replace('for', 'for-', strtolower($subpanelName));
        return 'subpanel-' . $newName;
    }

    /**
     * Convert a legacy subpanel path to the new sidecar path
     * @param string $filename the path to a legacy subpanel
     * @return string the new sidecar subpanel path
     */
    public function fromLegacySubpanelPath($fileName)
    {
        $pathInfo = pathinfo($fileName);

        $dirParts = explode(DIRECTORY_SEPARATOR, $pathInfo['dirname']);

        if (count($dirParts) < 3) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Directory '%s' is an incorrect path for a subpanel",
                    $fileName
                )
            );
        }

        $module = $dirParts[1];

        $customDir = '';
        if ($dirParts[0] == 'custom') {
            $customDir = 'custom/';
            $module = $dirParts[2];
        }
        $newSubpanelName = $this->fromLegacySubpanelName($pathInfo['filename']);
        return "{$customDir}modules/{$module}/clients/base/views/{$newSubpanelName}/{$newSubpanelName}.php";
    }
}