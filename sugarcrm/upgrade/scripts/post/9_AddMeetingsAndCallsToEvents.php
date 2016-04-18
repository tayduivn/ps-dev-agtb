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


use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;

/**
 * Add calls and meetings to events
 */
class SugarUpgradeAddMeetingsAndCallsToEvents extends UpgradeScript
{
    /**
     * {@inheritdoc}
     */
    public $order = 9998;

    /**
     * {@inheritdoc}
     * @var int
     */
    public $type = self::UPGRADE_DB;

    /**
     * Should prepare export for all records of all supported modules (Meetings, Calls, etc)
     *
     * {@inheritdoc}
     */
    public function run()
    {
        // This upgrade is for version lower than 7.8.0.0
        if (version_compare($this->from_version, '7.8.0.0RC4', '>=')) {
            return;
        }

        // Get all supported modules
        $factory = $this->getCalDavAdapterFactory();
        $modules = $factory->getSupportedModules();

        $this->db->query($this->db->truncateTableSQL("caldav_synchronization"));
        $this->db->query($this->db->truncateTableSQL("caldav_queue"));

        foreach ($modules as $module) {
            $verifyResult = true;
            $bean = BeanFactory::getBean($module);

            $properties = array(
                'repeat_root_id',
                'repeat_parent_id',
                'repeat_dow',
                'repeat_type',
            );

            foreach ($properties as $property) {
                if (isset($bean->field_defs[$property]['source']) && ($bean->field_defs['source'] == 'non-db')) {
                    $verifyResult = false;
                    break;
                }
            }

            $query = $this->getQuery();
            $query->from($bean);
            $query->select(array('id'));
            $query->orderBy('repeat_parent_id', 'ASC');
            $query->orderBy('date_start', 'ASC');
            $rows = $query->execute();

            foreach ($rows as $row) {
                $bean = BeanFactory::getBean($module, $row['id']);

                if ($verifyResult) {
                    if (!empty($bean->repeat_parent_id)) {
                        $bean->repeat_root_id = $bean->repeat_parent_id;
                    } else {
                        $bean->repeat_root_id = $bean->id;
                    }

                    if ($bean->repeat_type != 'Weekly') {
                        $bean->repeat_dow = '';
                    }
                }

                $bean->save(false, array('disableCalDavHook' => true));
                $bean->getCalDavHook()->export($bean);
            }
        }
    }

    /**
     * Returns Query class to work with
     *
     * @return SugarQuery
     */
    protected function getQuery()
    {
        return new SugarQuery();
    }

    /**
     * * Returns factory class to get list of supported modules
     *
     * @return CalDavAdapterFactory
     */
    protected function getCalDavAdapterFactory()
    {
        return new CalDavAdapterFactory();
    }
}
