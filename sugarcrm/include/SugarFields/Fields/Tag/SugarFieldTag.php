<?php
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
require_once 'include/SugarFields/Fields/Relatecollection/SugarFieldRelatecollection.php';

/**
 * The SugarFieldTag handles the tag field
 */
class SugarFieldTag extends SugarFieldRelatecollection
{
    /**
     * Override of parent apiSave to force the custom save to be run from API
     * @param SugarBean $bean
     * @param array     $params
     * @param string    $field
     * @param array     $properties
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties)
    {
        if (!is_array($params[$field])) {
            return;
        }

        // Loop through submitted Tags to make collection of  tag beans (either new or retrieved)
        $relBeans = array();
        foreach ($params[$field] as $key => $record) {
            // Collect all tag beans
            $relBeans[] = $this->getTagBean($record);
        }

        // get relationship name and load the relationship
        // then figure out the tags which have been added / deleted by comparing between
        // original tags and the submitted tags
        $relField = $properties['link'];

        if ($bean->load_relationship($relField)) {
            // get current tag beans on the record
            $currRelBeans = $bean->$relField->getBeans();

            // get the submitted values of the tags
            $changedTags = $this->getChangedTags($params, $field);

            // get list of original tags
            $originalTags = $this->getOriginalTags($currRelBeans);

            // Grab the changes from old to new
            list($addedTags, $removedTags) = $this->getChangedValues($originalTags, $changedTags);

            // Handle delete of tags
            $this->removeTagsFromBean($bean, $currRelBeans, $relField, $removedTags);

            // Handle adding new tags
            $this->addTagsToBean($bean, $relBeans, $relField, $addedTags);

        } else {
            $GLOBALS['log']->fatal("Failed to load relationship $relField on {$bean->module_dir}");
        }

    }

    /**
     * Retrieve a tagBean or Create a new one
     * @param Array containing tag id and tag name with the keys (id, name)
     * @return SugarBean
     */
    protected function getTagBean($record)
    {
        // We'll need this no matter what
        $tagBean = BeanFactory::getBean('Tags');

        if (!empty($record['id'])) {
            if ($tagBean->retrieve($record['id'])) {
                return $tagBean;
            }
        }

        // See if this tag exists already. If it does send back the bean for it
        $q = $this->getSugarQuery();
        $q->select(array('id', 'name'));
        $q->from($tagBean);
        // Get the tag from the lowercase version of the name
        $q->where()->equals('name_lower', strtolower($record['name']));
        $result = $q->execute();

        // If there is a result for this tag name, send back the bean for it
        if (!empty($result[0]['id'])) {
            if ($tagBean->retrieve($result[0]['id'])) {
                return $tagBean;
            }
        }

        // Create a new record and send back THAT bean
        $tagBean->fromArray(array('name' => $record['name']));
        $tagBean->save();
        return $tagBean;
    }

    /**
     * {@inheritDoc}
     */
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties)
    {
        $relField = $properties['link'];
        $tags = array();

        if ($bean->load_relationship($relField)) {

            $currRelBeans = $bean->$relField->getBeans();

            if (!empty($currRelBeans)) {
                foreach ($currRelBeans as $tagId => $tagRecord) {
                    $tags[] = array('id' => $tagId, 'name' => $tagRecord->name);
                }
            }
        }

        $data[$fieldName] = $tags;
    }


    /**
     * Gets an array of added and removed tags for a bean
     *
     * @param Array $first The initial array of values
     * @param Array $second The changed array of values
     * @return Array of added and removed tags
     */
    public function getChangedValues(Array $initial, Array $changed)
    {
        // Handle comparison on the keys
        $iKeys = array_keys($initial);
        $cKeys = array_keys($changed);
        // Added are what is in $changed that are not in $initial
        $a = array_diff($cKeys, $iKeys);
        // Removed are what is in $initial but not $changed
        $r = array_diff($iKeys, $cKeys);
        $added = $removed = array();
        foreach ($a as $add) {
            $added[$add] = $changed[$add];
        }
        foreach ($r as $rem) {
            $removed[$rem] = $initial[$rem];
        }
        return array($added, $removed);
    }

    /**
     * Gets an array of changed tags in the format tagname => tagname
     *
     * @param Array of Submitted Values
     * @param String - current field name (which would be "tag")
     * @return Array of Changed Tag Names
     */
    public function getChangedTags($params, $field)
    {
        $changedTags = array();
        if (!empty($params[$field])) {
            $submittedTags = $params[$field];
            foreach ($submittedTags as $submittedTag) {
                $changedTags[strtolower($submittedTag['name'])] = $submittedTag['name'];
            }
        }
        return $changedTags;
    }

    /**
     * Gets an Array of original tags in the format tagname => tagname
     *
     * @param Array of Original Tag Beans on the Record
     * @return Array of Original Tag Names
     */
    public function getOriginalTags($currRelBeans)
    {
        $originalTags = array();
        if (!empty($currRelBeans)) {
            foreach ($currRelBeans as $tagId => $tagRecord) {
                $originalTags[strtolower($tagRecord->name)] = $tagRecord->name;
            }
        }
        return $originalTags;
    }

    /**
     * Remove Tags from the Bean
     *
     * @param SugarBean - The Bean from which the Tags need to be disassociated
     * @param Array of Current Tag Beans on the Record
     * @param String - relationship field
     * @param Array of Removed Tag Names
     * @return Void
     */
    public function removeTagsFromBean($bean, $currRelBeans, $relField, $removedTags)
    {
        foreach ($currRelBeans as $currRelBean) {
            if (isset($currRelBean->name_lower) && isset($removedTags[$currRelBean->name_lower])) {
                if (!$bean->$relField->delete($bean->id, $currRelBean->id)) {
                    // Log to fatal
                    $GLOBALS['log']->fatal("Failed to delete tag {$currRelBean->name} from {$bean->module_dir}");
                }
            }
        }
    }

    /**
     * Add Tags to the Bean
     *
     * @param SugarBean - The Bean to which the Tags need to be associated
     * @param Array of Added Tag Beans on the Record
     * @param String - relationship field
     * @param Array of Added Tag Names
     * @return Void
     */
    public function addTagsToBean($bean, $relBeans, $relField, $addedTags)
    {
        foreach ($relBeans as $relBean) {
            if (isset($addedTags[$relBean->name_lower])) {
                if (!$bean->$relField->add($relBean)) {
                    // Log to fatal
                    $GLOBALS['log']->fatal("Failed to add tag {$relBean->name} as a relate to {$bean->module_dir}");
                }
            }
        }
    }

}
