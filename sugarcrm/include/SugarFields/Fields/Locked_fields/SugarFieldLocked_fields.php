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

require_once 'include/SugarFields/Fields/Relatecollection/SugarFieldRelatecollection.php';

use Sugarcrm\Sugarcrm\ProcessManager\Registry;
use Sugarcrm\Sugarcrm\ProcessManager;

/**
 * The SugarFieldLocked_field handles the Locked_field
 */
class SugarFieldLocked_fields extends SugarFieldRelatecollection
{
    /**
     * The key used in the related result set to get the data we need
     * @var string
     */
    protected $relateKey = 'pro_locked_variables';

    /**
     * {@inheritDoc}
     */
    public function apiFormatField(
        array &$data,
        SugarBean $bean,
        array $args,
        $fieldName,
        $properties,
        array $fieldList = null,
        ServiceBase $service = null
    ) {
        // This is the expectation from the FilterApi
        if (isset($args['rc_beans'])) {
            if (!empty($args['rc_beans'][$fieldName][$bean->id])) {
                $data[$fieldName] = $args['rc_beans'][$fieldName][$bean->id];
            } else {
                $data[$fieldName] = array();
            }
        } else {
            // This block is the expectation from the ModuleApi

            // If the skip flag is set, it will be true, so check if it is true
            // to determine if we need to even set locked fields
            if (Registry\Registry::getInstance()->get('skip_locked_field_checks') === true) {
                $data[$fieldName] = array();
            } else {
                // Get the necessary information from the relate collection parent
                list ($relName, $fields, $limit) = $this->parseProperties($properties);

                // This should never be the case, but to be safe...
                if (!in_array($this->relateKey, $fields)) {
                    $fields[] = $this->relateKey;
                }

                // Get the related records
                $linked =  $this->getLinkedRecords($bean, $relName, $fields, $limit);

                // Loop and set, making sure to handle the necessary logic for locked fields
                $locked = array();
                foreach ($linked as $key => $value) {
                    $locked = array_merge($locked, json_decode(html_entity_decode($value[$this->relateKey])));
                }

                // To make sure the client has no hiccups with dupes
                $data[$fieldName] = array_unique($locked);
            }
        }
    }

    /**
     *
     * {@inheritdoc}
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties)
    {
        // This method is a no-op, as there is no way to set locked fields on a
        // bean from outside the application
        return;
    }
}
