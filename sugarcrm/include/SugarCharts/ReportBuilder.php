<?php

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