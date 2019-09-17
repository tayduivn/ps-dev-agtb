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

namespace Sugarcrm\Sugarcrm\ProductDefinition\Config\Cache;

class DbCache implements CacheInterface
{
    /**
     * Product definition refresh interval in hours
     * It means how many hours system will wait before refresh product definition
     */
    const REFRESH_INTERVAL = 24;

    const TABLE_NAME = 'product_definition';

    /**
     * @var \DBManager
     */
    protected $db;

    /**
     * DbCache constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->db = \DBManagerFactory::getInstance();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function getCurrentDefinition():? string
    {
        $result = $this->getDefinition();
        if (empty($result)) {
            return null;
        }
        $dateTimeCreated = \DateTime::createFromFormat(
            \TimeDate::DB_DATETIME_FORMAT,
            $result['date_created'],
            new \DateTimeZone('UTC')
        );
        $dateTimeCreated->add(new \DateInterval(sprintf('PT%dH', static::REFRESH_INTERVAL)));
        $dateTimeNow = new \DateTime('now', new \DateTimeZone('UTC'));
        if ($dateTimeCreated < $dateTimeNow) {
            return null;
        }
        return $result['data'];
    }

    /**
     * @throws \Exception
     * @inheritDoc
     */
    public function getPreviousDefinition():? string
    {
        $result = $this->getDefinition();
        return $result['data'] ?? '';
    }

    /**
     * return definition actual product definition depends on interval
     * @throws \Exception
     * @return array|null
     */
    protected function getDefinition():? array
    {
        $conn = $this->db->getConnection();
        $result = $conn->executeQuery('SELECT date_created, data FROM ' . static::TABLE_NAME)->fetch();
        if (!$result) {
            return null;
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function set(string $data)
    {
        $this->db->commit();
        $this->db->query($this->db->truncateTableSQL(static::TABLE_NAME));
        $this->db->getConnection()->insert(static::TABLE_NAME, [
            'date_created' => (new \DateTime('now', new \DateTimeZone('UTC')))->format(\TimeDate::DB_DATETIME_FORMAT),
            'data' => $data,
        ]);
        $this->db->commit();
    }
}
