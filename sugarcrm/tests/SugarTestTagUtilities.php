<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'modules/Tags/Tag.php';

class SugarTestTagUtilities
{
    private static $createdTagIds = array();

    private function __construct()
    {
    }

    /**
     * Create a Tag for use in a Unit Test
     *
     * @param array $values - values you want to override
     *
     * @return SugarBean tag
     */
    public static function createTag($values = array())
    {
        $num = mt_rand();
        $defaults =
            array(
                'name' => 'SugarTag' . $num,
            );

        $values = array_merge($defaults, $values);
        $tag = BeanFactory::newBean('Tags');
        $tag->populateFromRow($values);
        self::$createdTagIds[] = $tag->save();

        return $tag;
    }

    /**
     * Remove all Tags for use in a Unit Test
     *
     * @return null
     */
    public static function removeAllCreatedTags()
    {
        $tagIds = self::$createdTagIds;
        $GLOBALS['db']->query('DELETE FROM tags WHERE id IN (\'' . implode("', '", $tagIds) . '\')');
    }

    /**
     * Delete tags M2M relationship data
     *
     * @param string $moduleName
     * @param string $beanId
     */
    public static function deleteM2MRelationships($moduleName, $beanId)
    {
        $sql = "DELETE FROM tag_bean_rel WHERE 
                bean_module = '$moduleName' AND 
                bean_id = '$beanId'";
        $GLOBALS['db']->query($sql);
    }
}
