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

/**
 * The SugarBean for Each worklog message, should be immutable.
 */
class Worklog extends Basic
{
    public $module_dir = 'Worklog';
    public $object_name = 'Worklog';
    public $table_name = 'worklog';
    public $new_schema = true;
    public $importable = true;

    /**
     * @inheritDoc
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * Sets the entry of this worklog message. Shall only be called while creating
     * new worklog message, not for editing
     * @param string $entry The entry of this worklog message
     * @modifies $this->entry
     * @effects Sets $this->entry to processed $entry
     */
    public function setEntry(string $entry)
    {
        $this->entry = $this->toDBFormat($entry);
    }

    /**
     * Sets the module of this worklog message. Shall only be called while creating
     * new worklog message, not for editing
     * @param string $module The module this worklog is associated to,
     *                       has to be an existing module
     * @modifies $this->module
     * @effects Set $this->module to $module
     * @return true When $module exists and added to $this->module successfully
     *         Otherwise false.
     */
    public function setModule(string $module)
    {
        if (!is_string(BeanFactory::getBeanClass($module))) {
            return false;
        }

        $this->module = $module;

        return true;
    }

    /**
     * Turns $entry to DB storage format
     * @param string $entry
     * @return The formatted $entry
     * NOTE: Serving as a space for further expansion for different display option,
     *       returning same string entry now
     */
    private function toDBFormat(string $entry)
    {
        return $entry;
    }


    /**
     * Gets all the worklog for every record id given
     *
     * @param $focus
     * @param $ids array of record ids
     * @return array
     */
    public function getRelatedModuleRecords($focus, $ids)
    {
        // No ids means nothing to do
        // Not use this in Worklog module, use only for other modules
        if (empty($ids) || ($focus == null) || ($focus->table_name === 'worklog')) {
            return array();
        }

        $query = new SugarQuery($this->db);
        $query->from($focus);
        $query->join('worklog_link');
        $query->select()->fieldRaw('worklog_id');
        $query->where()->in('record_id', $ids);
        $results = $query->execute();

        $returnArray = array();
        foreach ($results as $result) {
            $returnArray[] = $result['worklog_id'];
        }

        return $returnArray;
    }
}
