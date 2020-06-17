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
 * Class SugarUpgradeAlterKBVoteField.
 * Changes length to vardefs value of vote field in kbusefulness table.
 */
class SugarUpgradeFixFieldsMetaDataIdLength extends UpgradeScript
{
    public $order = 2001;
    public $type = self::UPGRADE_DB;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (version_compare($this->to_version, '10.1.0', '<')) {
            return;
        }
        $tracker = BeanFactory::newBean('Trackers');
        $fieldsMetaData = BeanFactory::newBean('EditCustomFields');
        $conn = $this->db->getConnection();
        $idLengthExpression = $this->db->convert('id', 'length');

        $query = new SugarQuery();
        $query->select(['id']);
        $query->from($fieldsMetaData, ['add_deleted' => false]);
        $query->where()->addRaw($idLengthExpression . '> 36');
        $stmt = $query->compile()->execute();
        while ($row = $stmt->fetch()) {
            $newId = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
            $oldId = $row['id'];
            $conn->update($fieldsMetaData->getTableName(), ['id' => $newId], ['id' => $oldId]);
            $conn->update($tracker->getTableName(), ['item_id' => $newId], ['item_id' => $oldId]);
            $this->db->commit();
        }
    }
}
