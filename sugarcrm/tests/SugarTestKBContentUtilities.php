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

require_once 'modules/KBContents/KBContent.php';

class SugarTestKBContentUtilities
{

    protected static $_createdBeans = array();

    private function __construct() {}

    public static function createBean($values = array())
    {
        $defaults = array(
            'name' => 'SugarKBContent' . time(),
        );

        $values = array_merge($defaults, $values);
        $bean = new KBContentMock();
        $bean->populateFromRow($values);
        $bean->save();
        DBManagerFactory::getInstance()->commit();
        self::$_createdBeans[] = $bean;
        return $bean;
    }

    public static function removeAllCreatedBeans()
    {
        $db = DBManagerFactory::getInstance();
        $beans = self::$_createdBeans;
        $ids = array();

        foreach ($beans as $bean) {
        	$ids[] = $bean->id;
        	$db->query('DELETE FROM kbdocuments WHERE id = ' . $db->quoted($bean->kbdocument_id));	
        	$db->query('DELETE FROM kbarticles WHERE id = ' . $db->quoted($bean->kbarticle_id));	
        }

        $conditions = implode(',', array_map(array($db, 'quoted'), $ids));
        $db->query('DELETE FROM kbcontents WHERE id IN (' . $conditions . ')');
        $db->query('DELETE FROM kbcontents_audit WHERE id IN (' . $conditions . ')');
        self::$_createdBeans = array();
    }
}

class KBContentMock extends KBContent
{
    public function resetActiveRevision()
    {
        $this->resetActiveRev();
    }
}
