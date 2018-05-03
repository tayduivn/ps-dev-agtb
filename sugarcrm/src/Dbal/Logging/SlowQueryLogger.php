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

namespace Sugarcrm\Sugarcrm\Dbal\Logging;

/**
 * Logs queries into sugarcrm log
 */
class SlowQueryLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * Logging level
     * @var string
     */
    const LEVEL = 'warn';

    /**
     * Maximum length of the parameter value to dump
     * @var int
     */
    const MAX_PARAM_LENGTH = 100;

    /**
     * Sugar log
     *
     * @var \LoggerManager
     */
    protected $logger;

    /**
     * @var float|null
     */
    protected $start = null;

    /**
     * If Slow Query Logging is enabled or not.
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * Query execution time threshold
     *
     * @var int
     */
    public $threshold = 2000;

    /**
     * @var array
     */
    public $currentQuery = array();

    /**
     * @param \LoggerManager $logger Sugar log, usually $GLOBALS['log']
     * @param boolean $enabled
     * @param integer $threshold
     */
    public function __construct(\LoggerManager $logger, $enabled, $threshold)
    {
        $this->logger = $logger;
        $this->enabled = $enabled;
        $this->threshold = $threshold;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($this->enabled && $this->logger->wouldLog(self::LEVEL)) {
            $this->start = microtime(true);
            $this->currentQuery = array('sql' => $sql, 'params' => $params, 'types' => $types, 'executionMS' => 0);
        }
    }

    public function stopQuery()
    {
        if ($this->enabled && $this->logger->wouldLog(self::LEVEL)) {
            $this->currentQuery['executionTime'] = microtime(true) - $this->start;
            if (($this->currentQuery['executionTime'] * 1000) >= $this->threshold) {
                $message = $this->getQueryLogMessage(
                    $this->currentQuery['sql'],
                    $this->currentQuery['params'],
                    $this->currentQuery['types'],
                    $this->currentQuery['executionTime']
                );
                $this->start = 0;
                $this->currentQuery = null;
                $this->log($message);
            }
        }
    }

    /**
     * @param array $message Array to log
     *
     * @return string
     */
    protected function stringify(array $message)
    {
        return json_encode(
            array_map(
                function ($str) {
                    if (is_string($str) && (strlen($str) > self::MAX_PARAM_LENGTH)) {
                        $str = substr($str, 0, self::MAX_PARAM_LENGTH) . '...';
                    }
                    return $str;
                },
                $message
            )
        );
    }

    /**
     * @param string $message
     */
    protected function log($message)
    {
        call_user_func(array($this->logger, self::LEVEL), $message);
    }

    /**
     * @param $sql
     * @param array $params
     * @param array $types
     * @param int $executionTime
     * @return string
     */
    protected function getQueryLogMessage($sql, array $params = null, array $types = null, float $executionTime)
    {
        $message = 'Slow Query (time:'
            . $executionTime
            . ' seconds)' . PHP_EOL . $sql;
        if (count($params)) {
            $message .= PHP_EOL . 'Params: ' . $this->stringify($params);
        }
        if (count($types)) {
            $message .= PHP_EOL . 'Types: ' . $this->stringify($types);
        }
        return $message;
    }
}
