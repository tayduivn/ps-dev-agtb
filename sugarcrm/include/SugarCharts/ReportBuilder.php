<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * PHP Report Builder.  This will create a report on the fly to run via the API
 */
class ReportBuilder
{

    /**
     * The Default Structure For A Report JSON
     *
     * @var array
     */
    protected $defaultReport = array(
        'display_columns' => array(),
        'module' => '',
        'group_defs' => array(),
        'summary_columns' => array(),
        'report_name' => '',
        'chart_type' => 'hBarF',
        'do_round' => true,
        'chart_description' => '',
        'numerical_chart_column' => 'count',
        'numerical_chart_column_type' => '',
        'assigned_user_id' => '1',
        'report_type' => 'summary',
        'full_table_list' => array(),
        'filters_def' => array(
        )
    );

    /**
     * An array of Created Bean
     *
     * @var array
     */
    protected $beans = array();

    /**
     * Mapping of Modules to Table Keys
     *
     * @var array
     */
    protected $table_keys = array();

    /**
     * Mapping of the Links to the Tables
     *
     * @var array
     */
    protected $link_keys = array();

    /**
     * What's the default module
     *
     * @var string
     */
    protected $self_module;

    /**
     * Class Constructor
     *
     * @param string $module    The Default Starting Module
     */
    public function __construct($module)
    {
        $this->self_module = $module;
        $this->defaultReport['module'] = $module;
        $this->addModule($this->self_module, 'self');
    }

    /**
     * Add module to the report
     *
     * @param string $module        The module we are adding
     * @param null|string $key      The key for the module we are adding
     * @return ReportBuilder
     */
    public function addModule($module, $key)
    {
        $bean = $this->getBean($module);

        $this->table_keys[$key] = array('module' => $module, 'key' => $key);

        $this->defaultReport['full_table_list'][$key] = array(
            'value' => $bean->module_dir,
            'module' => $bean->module_dir,
            'label' => $bean->module_dir,
            'parent' => '',
            'children' => array(),
        );

        return $this;
    }

    /**
     * Add A Field via a link, If the link doesn't load it will not process the link in the chart.
     *
     * @param string $link              The Link name to load the field from
     * @param string $field             The field to add to the group by
     * @param string|array $path        The Parent module for the link, this can be a string or an array with the path to the new link
     * @return ReportBuilder
     */
    public function addLink($link, $field = null, $path = null)
    {
        if (empty($path)) {
            $path = array($this->self_module);
        } else if (!is_array($path)) {
            $path = array($path);
        }

        $last_item = array_pop(array_values($path));
        if ($last_item !== $link) {
            array_push($path, $link);
        }

        $key = array();
        $module = null;

        $last_item = array_pop(array_values($path));
        foreach ($path as $step) {
            if (empty($module) && ($step == $this->self_module || $step == "self")) {
                $module = $this->getDefaultModule(true);
                $key[] = $step;
            } else {
                // make this module
                if (!($module instanceof SugarBean)) {
                    $module = $this->getBean($module);
                }

                $_bean_links = $module->get_linked_fields();
                if (isset($_bean_links[$step])) {
                    // we have a link
                    // get the final module and set it
                    $tmp_module = $this->getBean($_bean_links[$step]['module']);
                    if ($tmp_module !== false) {

                        $_field = null;
                        if ($last_item == $step && isset($tmp_module->field_defs[$field])) {
                            // make sure the field exists
                            $_field = $field;
                        }

                        $key[] = $step;

                        // now add the link
                        $this->_addLink($step, join(":", $key), $module, $_field);

                        $module = $tmp_module;

                        continue;
                    }
                } else {
                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * @param string $link
     * @param string $key
     * @param SugarBean $bean
     * @param string $field
     */
    protected function _addLink($link, $key, $bean, $field = null)
    {
        $links = $bean->get_linked_fields();

        if (isset($links[$link]) && $bean->load_relationship($link)) {
            // we have the link
            /* @var $bean_rel Link2 */
            $bean_rel = $bean->$link;

            $link = $links[$link];

            if (empty($key)) {
                $key = $bean->module_dir . ':' . $link['name'];
            } elseif (is_array($key)) {
                $key = join(":", $key);
            }

            //$child_bean = $this->getBean($link['module']);
            $this->table_keys[$key] = array('module' => $link['module'], 'key' => $key);
            //$this->table_keys[$link['module']] = $key;
            $this->link_keys[$link['name']] = $key;
            if (!is_null($field)) {
                $this->addGroupBy($field, $link['module'], $key);
            }

            if (!isset($this->defaultReport['full_table_list'][$key])) {
                $parent = $this->findParentTableKey($bean_rel->getRelatedModuleName(), $key, $field);

                $arrLink = array(
                    'name' => $bean->module_dir . ' > ' . $link['module'],
                    'parent' => $parent,
                    'children' => array(),
                    'link_def' => array(
                        'name' => $link['name'],
                        'relationship_name' => $link['relationship'],
                        'bean_is_lhs' => ($bean_rel->getSide() == 'LHS'),
                        'link_type' => $bean_rel->getType(),
                        'label' => $link['module'],
                        'module' => $link['module'],
                        'table_key' => $key,
                    ),
                    'dependents' => array(),
                    'module' => $link['module'],
                    'label' => $link['vname']
                );

                $this->defaultReport['full_table_list'][$key] = $arrLink;
            }
        }
    }

    /**
     * Utility Method for finding the parent of the field/module combo
     *
     * @param string $bean_name         Module Name
     * @param string $key               Potential Key name we are working with
     * @param null|string $field        Do we have a field we are working with?
     * @return array|string
     */
    protected function findParentTableKey($bean_name, $key, $field = null)
    {
        $parent = "";
        $potentialParents = $this->getKeyTable($bean_name);
        if (is_array($potentialParents)) {
            if (empty($field) && isset($potentialParents[$key])) {
                unset($potentialParents[$key]);
            } elseif (!empty($field) && isset($potentialParents[$key])) {
                $parent = $key;
            }

            // for now take the first one on what's left
            if (empty($parent)) {
                if (count($potentialParents) >= 1) {
                    $parent = array_shift(array_keys($potentialParents));
                } else {
                    // it's empty, so just set it to self;
                    $parent = "self";
                }
            }
        } else {
            $parent = $potentialParents;
        }

        return $parent;
    }

    /**
     * Add A Field To Group By
     *
     * @param string $field         Which field do we want to group by
     * @param string|null $module   Which module the field belongs to
     * @param string|null $key      Potential Key that we are working with
     * @return ReportBuilder
     */
    public function addGroupBy($field, $module = null, $key = null)
    {
        if (empty($module)) {
            $module = $this->self_module;
        }
        $bean = $this->getBean($module);

        if (isset($bean->field_defs[$field])) {
            $bean_field = $bean->field_defs[$field];

            $this->defaultReport['group_defs'][] = array(
                'name' => $field,
                'label' => $bean_field['vname'],
                'table_key' => $this->findParentTableKey($bean->module_dir, $key, $field),
                'type' => $bean_field['type'],
            );

            $this->addSummaryColumn($field, $bean, $key);
        }

        return $this;
    }

    /**
     * Add a Column to the Summary Output
     *
     * @param string $field         Which field to add
     * @param string|null $module   Which module does the field belong to
     * @param string|null $key      Potential Key that we are working with
     * @return ReportBuilder
     */
    public function addSummaryColumn($field, $module = null, $key = null)
    {
        if (!($module instanceof SugarBean)) {
            if (empty($module)) {
                $module = $this->self_module;
            }
            $bean = $this->getBean($module);
        } else {
            $bean = $module;
            $module = $bean->module_dir;
        }

        if (isset($bean->field_defs[$field])) {
            $bean_field = $bean->field_defs[$field];

            $this->defaultReport['summary_columns'][] = array(
                'name' => $field,
                'label' => $bean_field['vname'],
                'table_key' => $this->findParentTableKey($module, $key, $field),
            );
        }

        return $this;
    }

    /**
     * Add A Count Column to the Summary
     *
     * @return ReportBuilder
     */
    public function addSummaryCount()
    {
        $this->defaultReport['summary_columns'][] = array(
            'name' => 'count',
            'label' => 'Count',
            'table_key' => 'self',
            'group_function' => "count",
            'field_type' => ''
        );

        return $this;
    }

    /**
     * Add Filter
     *
     * @param $filter
     */
    public function addFilter($filter)
    {
        $this->defaultReport['filters_def'] = $filter;
    }

    /**
     * Return the Data as a JSON String
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->defaultReport);
    }

    /**
     * Return the report as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->defaultReport;
    }

    /**
     * Get the SugarBean
     *
     * @param string $module    Which Module To Load
     * @return SugarBean
     */
    public function getBean($module)
    {
        if (!isset($this->beans[$module])) {
            $this->beans[$module] = BeanFactory::getBean($module);
        }

        return $this->beans[$module];
    }

    /**
     * Return a specify key if a module is set, if not return the whole array
     *
     * @param string $module        Specific module to get a table key for.
     * @return array|string
     */
    public function getKeyTable($module = null)
    {
        // find all the array that match the current module
        $found = array();

        foreach ($this->table_keys as $key => $map) {
            if ($map['module'] == $module) {
                $found[$key] = $map;
            }
        }

        // if we found none, return the whole array
        if (empty($found)) {
            return $this->table_keys;
        }

        // if we only have one return the key
        if (count($found) == 1) {
            return array_shift(array_keys($found));
        }

        // just return all found.
        return $found;

    }

    /**
     * Convert A Table Key into a SugarBean
     *
     * @param string $tableKey          Table key we are working with
     * @return SugarBean|boolean
     */
    public function getBeanFromTableKey($tableKey)
    {
        $module = false;
        foreach ($this->table_keys as $key => $map) {
            if ($key == $tableKey) {
                $module = $map['module'];
                break;
            }
        }
        return ($module === false) ? false : $this->getBean($module);
    }

    /**
     * Return a specify key if a link is passed in, if not return the whole array
     *
     * @param string $link        Specific link to get a table key for.
     * @return array|string
     */
    public function getLinkTable($link = null)
    {
        if (is_null($link) || !isset($this->link_keys[$link])) {
            return $this->link_keys;
        } else {
            return $this->link_keys[$link];
        }
    }

    /**
     * Return the default module set.
     *
     * @param boolean $asBean       Return the default module as a SugarBean instance
     * @return string|SugarBean
     */
    public function getDefaultModule($asBean = false)
    {
        if ($asBean == true) {
            return $this->getBean($this->self_module);
        }

        return $this->self_module;
    }

    /**
     * Change the chart type,  If the value is not valid, it will default to hBarF.
     *
     * @param $chartType
     */
    public function setChartType($chartType)
    {
        $validCharts = array('hBarF', 'vBarF', 'pieF', 'lineF', 'funnelF');

        if(in_array($chartType, $validCharts)) {
            $this->defaultReport['chart_type'] = $chartType;
        } else {
            $this->defaultReport['chart_type'] = 'hBarF';
        }
    }

    /**
     * Return the ChartType Setting
     *
     * @return string
     */
    public function getChartType()
    {
        return $this->defaultReport['chart_type'];
    }
}