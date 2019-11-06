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

namespace Sugarcrm\Sugarcrm\Denormalization\Relate;

use Administration;
use SugarBean;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Db\Db;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Db\OnlineOperations;

/**
 *
 * Logic hook handler
 *
 */
final class HookHandler
{
    /** @var array */
    private static $settingsCache;

    /** @var OnlineOperations */
    private $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * To be used from logic hooks
     *
     * @param SugarBean $bean
     * @param string $event Triggered event
     * @param array $arguments Optional arguments
     */
    public function handleBeforeUpdate(?SugarBean $bean, string $event, array $arguments): void
    {
        foreach ($this->getSettings($bean) as $sourceLinkedFieldName => $options) {
            if ($options['is_main']) {
                $this->db->updateLinkedBean(
                    $bean,
                    $options['link']['linked_field_name'],
                    $options['link']['linked_key'],
                    $options['link']['join_table'],
                    $options['link']['join_main_key'],
                    $options['link']['join_linked_key'],
                    $options['denorm_field_name'],
                    $options['link']['main_table'],
                    $options['link']['main_key']
                );
                if (!empty($options['synchronization_in_progress'])) {
                    $this->db->updateTemporaryTable(
                        $bean,
                        $options['link']['linked_field_name'],
                        $options['link']['linked_table'],
                        $options['link']['linked_key']
                    );
                }
            } else {
                $bean->{$options['denorm_field_name']} = $bean->$sourceLinkedFieldName;
                if (!empty($options['synchronization_in_progress'])) {
                    $this->db->updateTemporaryTableWithValue($bean, $bean->$sourceLinkedFieldName);
                }
            }
        }
    }

    /**
     * To be used from logic hooks
     *
     * @param SugarBean $bean
     * @param string $event Triggered event
     * @param array $arguments Optional arguments
     */
    public function handleAfterUpdate(?SugarBean $bean, string $event, array $arguments): void
    {
        foreach ($this->getSettings($bean) as $sourceLinkedFieldName => $options) {
            // "track_field" uses to track direct bean->link_id modification and update appropriate denorm field
            if (isset($options['track_field']) && isset($arguments['dataChanges'][$options['track_field']])) {
                if (!empty($bean->{$options['track_field']}) && !$options['is_main']) {
                    // "track field" value changed and now we have to update related field
                    $this->db->updateBeanWithLinkId(
                        $bean,
                        $options['link']['linked_field_name'],
                        $options['link']['linked_table'],
                        $options['link']['linked_key'],
                        $options['link']['main_table'],
                        $options['denorm_field_name'],
                        $bean->{$options['track_field']}
                    );
                    if (!empty($options['synchronization_in_progress'])) {
                        $this->db->updateTemporaryTable(
                            $bean,
                            $options['link']['linked_field_name'],
                            $options['link']['linked_table'],
                            $options['link']['linked_key']
                        );
                    }
                }
            }
        }
    }

    /**
     * To be used from logic hooks
     *
     * @param SugarBean $bean
     * @param string $event Triggered event
     * @param array $arguments Optional arguments
     */
    public function handleDeleteRelationship(?SugarBean $bean, string $event, array $arguments): void
    {
        foreach ($this->getSettings($bean) as $sourceLinkedFieldName => $options) {
            if ($options['module'] !== $arguments['related_module']) {
                continue;
            }

            if (!$options['is_main']) {
                $bean->{$options['denorm_field_name']} = '';
                $this->db->updateBean($bean, $options['denorm_field_name']);
                if (!empty($options['synchronization_in_progress'])) {
                    $this->db->updateTemporaryTable(
                        $bean,
                        $options['link']['linked_field_name'],
                        $options['link']['linked_table'],
                        $options['link']['linked_key']
                    );
                }
            }
        }
    }

    /**
     * To be used from logic hooks
     *
     * @param SugarBean $bean
     * @param string $event Triggered event
     * @param array $arguments Optional arguments
     */
    public function handleAddRelationship(?SugarBean $bean, string $event, array $arguments): void
    {
        foreach ($this->getSettings($bean) as $sourceLinkedFieldName => $options) {
            if ($options['module'] !== $arguments['related_module']) {
                continue;
            }
            if (!$options['is_main']) {
                $bean->{$options['denorm_field_name']} = $bean->$sourceLinkedFieldName;
                if (!empty($options['synchronization_in_progress'])) {
                    $this->db->updateTemporaryTableWithValue($bean, $bean->$sourceLinkedFieldName);
                }
            } else {
                $this->db->updateLinkedBean(
                    $bean,
                    $options['link']['linked_field_name'],
                    $options['link']['linked_key'],
                    $options['link']['join_table'],
                    $options['link']['join_main_key'],
                    $options['link']['join_linked_key'],
                    $options['denorm_field_name'],
                    $options['link']['main_table'],
                    $options['link']['main_key']
                );
                if (!empty($options['synchronization_in_progress'])) {
                    $this->db->updateTemporaryTable(
                        $bean,
                        $options['link']['linked_field_name'],
                        $options['link']['linked_table'],
                        $options['link']['linked_key']
                    );
                }
            }
        }
    }

    /**
     * Used in tests
     */
    public static function clearCache(): void
    {
        self::$settingsCache = null;
    }

    protected function getSettings($bean): array
    {
        if (!$bean instanceof SugarBean) {
            return [];
        }

        if (is_null(self::$settingsCache)) {
            self::$settingsCache = Administration::getSettings('denormalization')->settings['denormalization_fields']
                ?? [];
        }

        $settings = self::$settingsCache[$bean->getModuleName()] ?? [];

        return $settings;
    }
}
