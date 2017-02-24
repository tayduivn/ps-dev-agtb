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

namespace Sugarcrm\Sugarcrm\Dbal\Mysqli;

use Doctrine\DBAL\Driver\Mysqli\MysqliException;
use Doctrine\DBAL\Driver\Mysqli\MysqliStatement as BaseStatement;

/**
 * MySQLi statement
 */
class Statement extends BaseStatement
{
    /**
     * @var int
     * @link https://dev.mysql.com/doc/refman/5.7/en/error-messages-server.html#error_er_need_reprepare
     */
    const ER_NEED_REPREPARE = 1615;

    /**
     * @var string
     */
    protected $sql;

    /**
     * {@inheritDoc}
     */
    public function __construct(\mysqli $conn, $sql)
    {
        parent::__construct($conn, $sql);

        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MysqliException
     */
    public function execute($params = null)
    {
        $isFirstExecution = $this->_columnNames === null;
        $result = $this->tryToExecute($params);

        $hasColumns = $this->_columnNames !== false;

        // @link https://github.com/doctrine/dbal/pull/2536
        if (!$isFirstExecution && $hasColumns) {
            $this->_stmt->store_result();

            $this->_rowBindedValues = array_fill(0, count($this->_columnNames), null);

            $refs = array();
            foreach ($this->_rowBindedValues as $key => &$value) {
                $refs[$key] =& $value;
            }

            if (!call_user_func_array(array($this->_stmt, 'bind_result'), $refs)) {
                throw new MysqliException($this->_stmt->error, $this->_stmt->sqlstate, $this->_stmt->errno);
            }
        }

        return $result;
    }

    /**
     * Tries to execute the statement. In case of getting "Prepared statement needs to be re-prepared" error,
     * tries re-preparing the statement and executing it once again
     *
     * @param array|null $params An array of values with as many elements as there are
     *                           bound parameters in the SQL statement being executed.
     * @return boolean TRUE on success or FALSE on failure.
     * @throws MysqliException
     */
    protected function tryToExecute(array $params = null)
    {
        try {
            return parent::execute($params);
        } catch (MysqliException $e) {
            if ($e->getErrorCode() !== self::ER_NEED_REPREPARE) {
                throw $e;
            }

            $this->_stmt = $this->_conn->prepare($this->sql);

            if (false === $this->_stmt) {
                throw new MysqliException($this->_conn->error, $this->_conn->sqlstate, $this->_conn->errno);
            }

            return parent::execute($params);
        }
    }
}
