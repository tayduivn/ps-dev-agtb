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

class SugarTestPurchaseUtilities
{
    protected static $createdPurchases = array();

    /**
     * @return Purchase
     */
    public static function createPurchase($id = '')
    {
        $time = mt_rand();
        $timedate = TimeDate::getInstance();
        $purchase = BeanFactory::newBean('Purchases');

        $purchase->name = 'SugarPurchase' . $time;
        $purchase->date_entered = $timedate->getNow()->asDbDate();

        if (!empty($id)) {
            $purchase->new_with_id = true;
            $purchase->id = $id;
        }

        $purchase->save();
        self::$createdPurchases[] = $purchase;
        $purchase->load_relationship('purchasedlineitems');
        return $purchase;
    }

    public static function removeAllCreatedPurchases()
    {
        $db = DBManagerFactory::getInstance();

        $conditions = implode(',', array_map(array($db, 'quoted'), self::getCreatedPurchaseIds()));
        if (!empty($conditions)) {
            $db->query('DELETE FROM purchases_audit WHERE parent_id IN (' . $conditions . ')');
            $db->query('DELETE FROM purchases WHERE id IN (' . $conditions . ')');
        }
        self::$createdPurchases = array();
    }

    public static function getCreatedPurchaseIds()
    {
        $purchase_ids = array();
        foreach (self::$createdPurchases as $purchase) {
            $purchase_ids[] = $purchase->id;
        }
        return $purchase_ids;
    }
}
