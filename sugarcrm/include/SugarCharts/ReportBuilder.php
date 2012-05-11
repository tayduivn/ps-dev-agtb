<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
            'Filter_1' => array(
                'operator' => 'AND',
            )
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
    public function addModule($module, $key = null)
    {
        $bean = $this->getBean($module);

        if (empty($key)) {
            // we need to generate keys
        }

        $this->table_keys[$module] = $key;

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
     * @param string $module            The Parent module for the link.
     * @return ReportBuilder
     */
    public function addLink($link, $field, $module = null)
    {
        if(empty($module)) {
            $module = $this->self_module;
        }
        $bean = $this->getBean($module);

        $links = $bean->get_linked_fields();

        if(isset($links[$link]) && $bean->load_relationship($link)) {
            // we have the link
            /* @var $bean_rel Link2 */
            $bean_rel = $bean->$link;

            $link = $links[$link];

            $key = $bean->module_dir . ':' . $link['name'];

            //$child_bean = $this->getBean($link['module']);
            $this->table_keys[$link['module']] = $key;
            $this->addGroupBy($field, $link['module']);

            $field_position = count($this->defaultReport['summary_columns']);

            $arrLink = array(
                'name' => $bean->module_dir . ' > ' . $link['module'],
                'parent' => $this->table_keys[$module],
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
                'dependents' => array(
                    'group_by_row_' . $field_position,
                    'display_summaries_row_group_by_row_' . $field_position,
                ),
                'module' => $link['module'],
                'label' => $link['vname']
            );

            $this->defaultReport['full_table_list'][$key] = $arrLink;
        }

        return $this;
    }

    /**
     * Add A Field To Group By
     *
     * @param string $field         Which field do we want to group by
     * @param null|string $module   Which module the field belongs to
     * @return ReportBuilder
     */
    public function addGroupBy($field, $module = null)
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
                'table_key' => $this->table_keys[$module],
                'type' => $bean_field['type'],
            );

            $this->addSummaryColumn($field, $bean);
        }

        return $this;
    }

    /**
     * Add a Column to the Summary Output
     *
     * @param string $field         Which field to add
     * @param string $module        Which module does the field belong to
     * @return ReportBuilder
     */
    public function addSummaryColumn($field, $module)
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
                'table_key' => $this->table_keys[$module],
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

    public function addFilter()
    {

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
     * @param string $module Which Module To Load
     * @return SugarBean
     */
    protected function getBean($module)
    {
        if (!isset($this->beans[$module])) {
            $this->beans[$module] = BeanFactory::getBean($module);
        }

        return $this->beans[$module];
    }
}