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

use Sugarcrm\Sugarcrm\ProcessManager;

/**
 * Exports a record of a table in the database
 *
 * This class exports a record from a table in the database to a file
 * by encrypting its contents, to be transported from one instance to another.
 * @package PMSE
 */
class PMSEExporter
{
    /**
     * @var $bean
     * @access private
     */
    protected $bean;
    /**
     * @var $id
     * @access private
     */
    protected $id;
    /**
     * @var $uid
     * @access private
     */
    protected $uid;
    /**
     * @var $name
     * @access private
     */
    protected $name;
    /**
     * @var $extension
     * @access private
     */
    protected $extension;

    /**
     * list of dependencies for export
     * @var array
     */
    protected $dependencies = [
        'email_template',
        'business_rule',
    ];

    /**
     * Set Bean.
     * @codeCoverageIgnore
     * @param object $bean
     * @return void
     */
    public function setBean($bean)
    {
        $this->bean = $bean;
    }

    /**
     * Set UID.
     * @codeCoverageIgnore
     * @param string $uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Set Name.
     * @codeCoverageIgnore
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set Extension.
     * @codeCoverageIgnore
     * @param string $extension
     * @return void
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Method to download a file exported
     * @codeCoverageIgnore
     */
    public function exportProject($id, ServiceBase $api)
    {
        $projectContent = $this->getProject(array('id' => $id));

        // add dependencies when exporting a process definition
        if ($this->bean instanceof pmse_Project) {
            $projectContent = $this->addDependencies($projectContent);
        }

        //File Name
        $filename = str_replace(' ', '_', $projectContent['project'][$this->name]) . '.' . $this->extension;

        $api->setHeader("Content-Disposition", "attachment; filename=" . $filename);
        $api->setHeader("Content-Type", "application/" . $this->extension);
        $api->setHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $api->setHeader("Last-Modified", TimeDate::httpTime());
        $api->setHeader("Cache-Control", "max-age=0");
        $api->setHeader("Pragma", "public");

        return json_encode($projectContent);
    }

    /**
     * Adds the dependencies like email templates and business rules when exporting
     * a process definition
     * @param array $projectContent
     * @return array
     */
    private function addDependencies(array $projectContent)
    {
        foreach ($this->dependencies as $dependency) {
            // get the related email template and business rules ids
            if ($ids = $this->getDependentElementIds($dependency, $projectContent)) {
                // now add the content for import
                $dependencyContent = $this->getDependentContent($ids, $dependency);
                // no point adding dependencies if there isn't any
                if (!empty($dependencyContent)) {
                    $projectContent['dependencies'][$dependency] = $dependencyContent;
                }
            }
        }
        return $projectContent;
    }

    /**
     * Grabs the email template or business rule ids for the associated process definition id
     * @param string $dependency email_template or business_rule
     * @param array $projectContent process definition content
     * @return array element ids array
     */
    private function getDependentElementIds(string $dependency, array $projectContent)
    {
        $ids = array();

        switch ($dependency) {
            case 'email_template':
                $activities = $projectContent['project']['diagram']['events'];
                foreach ($activities as $activity) {
                    if ($activity['evn_marker'] == 'MESSAGE' && $activity['evn_behavior'] == 'THROW') {
                        // we don't wanna add null values
                        if (!empty($activity['def_evn_criteria'])) {
                            $ids[$activity['def_evn_criteria']] = $activity['def_evn_criteria'];
                        }
                    }
                }
                break;
            case 'business_rule':
                $activities = $projectContent['project']['diagram']['activities'];
                foreach ($activities as $activity) {
                    if ($activity['act_script_type'] == 'BUSINESS_RULE') {
                        // we don't wanna add null values
                        if (!empty($activity['def_act_fields'])) {
                            $ids[$activity['def_act_fields']] = $activity['def_act_fields'];
                        }
                    }
                }
                break;
            default:
        }

        return $ids;
    }

    /**
     * Grabs the content for email template/business rules
     * @param array $ids email template or business rule ids
     * @param string $type exporter type
     * @return array content
     */
    public function getDependentContent(array $ids, string $type)
    {
        $content = array();
        foreach ($ids as $value) {
            // get the exporter type
            $exporter = $this->getExporter($type);
            // we don't wanna add metadata again
            $projectData = $exporter->getProject(array('id' => $value, 'project_only' => true));
            if (isset($projectData['project'])) {
                $content[] = $projectData['project'];
            }
        }
        return $content;
    }

    /**
     * Gets the exporter for the specified type
     * @param string $type
     * @return ProcessManager\PMSE
     */
    public function getExporter(string $type)
    {
        // because we need to format the exporter name since `_` isn't valid
        // in case of email templates and business rules
        return ProcessManager\Factory::getPMSEObject(str_replace('_', '', ucwords($type, '_')) . 'Exporter');
    }

    /**
     * Method to retrieve a record of the database to export.
     * @param array $args
     * @return array
     */
    public function getProject(array $args)
    {
        $this->retrieveBean($args);

        if ($this->bean->fetched_row != false) {
            if (empty($args['project_only'])) {
                // send both metadata and project as requested
                return array('metadata' => $this->getMetadata(), 'project' => $this->bean->fetched_row);
            }
            // because import has a specific format and it doesn't want metadata or project
            return array('project' => $this->bean->fetched_row);
        } else {
            return array('error' => true);
        }
    }

    public function retrieveBean($args)
    {
        return $this->bean->retrieve($args['id']);
    }
    /**
     * Method to retrieve a metadata
     * @return object
     */
    public function getMetadata()
    {
        global $sugar_flavor, $sugar_version, $sugar_config;
        $toolName = 'ProcessAuthor';
        $toolVersion = '2.0';
        $metadataObject = array();
        $metadataObject['SugarCRMFlavor'] = $sugar_flavor;
        $metadataObject['SugarCRMVersion'] = $sugar_version;
        $metadataObject['SugarCRMHost'] = $sugar_config['host_name'];
        $metadataObject['SugarCRMUrl'] = $sugar_config['site_url'];
        $metadataObject['Name'] = $toolName;
        $metadataObject['Version'] = $toolVersion;
        $metadataObject['ExportDate'] = date('Y-m-d H:i:s');
        return $metadataObject;
    }
}