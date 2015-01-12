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


class PMSESettings
{
    /**
     * Save the instance of class
     *
     * @var
     */
    private static $instance;

    private $log_level = array(
        'emergency' => 'EMERGENCY',
        'alert' => 'ALERT',
        'critical' => 'CRITICAL',
        'error' => 'ERROR',
        'warning' => 'WARNING',
        'notice' => 'NOTICE',
        'info' => 'INFO',
        'debug' => 'DEBUG'
    );

    protected $settings_default = array(
        'logger_level' => array(
            'name' => 'logger_level',
            'description' => 'Logger Level',
            'value' => 'info',
            'type' => 'combobox',
            'items' => array(
                'emergency' => 'EMERGENCY',
                'alert' => 'ALERT',
                'critical' => 'CRITICAL',
                'error' => 'ERROR',
                'warning' => 'WARNING',
                'notice' => 'NOTICE',
                'info' => 'INFO',
                'debug' => 'DEBUG'
            ),
            'status' => 'Active'
        ),
        'error_number_of_cycles' => array(
            'name' => 'error_number_of_cycles',
            'description' => 'Number of cycles before trigger error',
            'value' => '10',
            'type' => 'text',
            'status' => 'Active'
        ),
        'error_timeout' => array(
            'name' => 'error_timeout',
            'description' => 'Time in seconds of timeout before trigger error',
            'value' => '40',
            'type' => 'text',
            'status' => 'Active'
        ),
    );

    private $settings_html = array();

    private $settings = array();

    public function __construct()
    {
        if (!isset($_SESSION['PMSESettings'])) {
            list($settingsEngine, $settingsHtml) = $this->getSettingsDB();
            $this->setSettings($settingsEngine);
            $this->setSettingsHtml($settingsHtml);
        } else {
            $this->setSettings($_SESSION['PMSESettings']);
        }
    }

    /**
     * Retrieve unique instance of the PMSELogger singleton
     * @return type
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function getSettingsDB()
    {
        $result_html = array();
        $result_engine = array();
        $beanSettings = BeanFactory::getBean("pmse_BpmConfig");
        $orderBy = "";
        $where = "cfg_status = 'Active'";
        $rows = $beanSettings->get_full_list($orderBy, $where);
        if (!empty($rows)) {
            foreach ($rows as $key => $row) {
                $result_engine[$row->name] = $row->cfg_value;
                $result_html[] = array_merge($this->settings_default[$row->name], array('value' => $row->cfg_value));
            }
        } else {
            $this->postSettingsDefault();
            list($result_engine, $result_html) = $this->getSettingsDB();
        }
        return array($result_engine, $result_html);
    }

    public function postSettingsDefault()
    {
        foreach ($this->settings_default as $value) {
            $beanS = BeanFactory::newBean('pmse_BpmConfig');
            $beanS->name = $value['name'];
            $beanS->description = $value['description'];
            $beanS->cfg_status = $value['status'];
            $beanS->cfg_value = $value['value'];
            $beanS->save();
        }
        $this->setSettingsHtml($this->settings_default);
    }

    public function putSettings($data = array())
    {
        $result = array();
        foreach ($data as $key => $value) {
            $beanSettings = BeanFactory::getBean('pmse_BpmConfig')
                ->retrieve_by_string_fields(array('name' => $key));
            if ($beanSettings->fetched_row) {
                $beanSettings->cfg_value = $value;
                $beanSettings->save();
                $result[$key] = $value;
            }
        }
        $this->setSettings($result);
    }

    public function setSettingsHtml($settings = array())
    {
        $this->settings_html = $settings;
    }

    public function getSettingsHtml()
    {
        return $this->settings_html;
    }


    public function setSettings($settings = array())
    {
        $this->settings = $settings;
        $_SESSION['PMSESettings'] = $settings;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}