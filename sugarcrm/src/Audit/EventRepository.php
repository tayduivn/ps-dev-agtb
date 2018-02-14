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

namespace Sugarcrm\Sugarcrm\Audit;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use InvalidArgumentException;
use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Util\Uuid;
use TimeDate;

class EventRepository
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Context
     */
    private $context;

    /**
     * Constructor
     *
     * @param Connection $conn
     * @param Context $context
     */
    public function __construct(Connection $conn, Context $context)
    {
        $this->conn = $conn;
        $this->context = $context;
    }

    /**
     * Registers update in EventRepository. Then saves audited fields.
     * @param SugarBean $bean
     * @param FieldList $changedFields
     * @return string id of audit event created
     * @throws DBALException
     */
    public function registerUpdate(SugarBean $bean, FieldList $changedFields)
    {
        return $this->save($bean, 'update', $changedFields);
    }

    /**
     * Registers erasure EventRepository. Then saves audited fields.
     * @param SugarBean $bean
     * @param FieldList $fields list of fields to be erased
     * @return string id of audit event created
     * @throws DBALException
     * @throws InvalidArgumentException
    */
    public function registerErasure(SugarBean $bean, FieldList $fields)
    {
        if (count($fields) === 0) {
            throw new InvalidArgumentException("Fields to be erased can not be empty.");
        }
        return $this->save($bean, 'erasure', $fields);
    }

    /**
     * Saves EventRepository
     * @param SugarBean $bean SugarBean that was changed
     * @param $eventType Audit event type
     * @param FieldList $fields list of fields impacted
     * @return string id of record saved
     * @throws DBALException
     */
    private function save(SugarBean $bean, $eventType, FieldList $fields)
    {
        $id =  Uuid::uuid1();

        $this->conn->insert(
            'audit_events',
            ['id' => $id,
            'type' => $eventType,
            'parent_id' => $bean->id,
            'module_name' => $bean->module_name,
            'source' => json_encode($this->context),
            'data' => json_encode($fields),
            'date_created' => TimeDate::getInstance()->nowDb(),]
        );

        return $id;
    }
}
