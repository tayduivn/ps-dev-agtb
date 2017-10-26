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

namespace Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm;

use DomainException;

final class Tables
{
    const STATE_VARIABLE = 'active_table';

    /**
     * @var string[]
     */
    private $tables;

    /**
     * @var string|null
     */
    private $activeTable;

    /**
     * @var State
     */
    private $state;

    public function __construct($table1, $table2, State $state)
    {
        $this->tables = [
            $table1 => true,
            $table2 => true,
        ];

        $this->state = $state;

        $activeTable = $this->state->get(self::STATE_VARIABLE);

        if ($activeTable !== null && $this->isValidTable($activeTable)) {
            $this->activeTable = $activeTable;
        }
    }

    public function getActive()
    {
        return $this->activeTable;
    }

    public function activate($activeTable)
    {
        if (!$this->isValidTable($activeTable)) {
            throw new DomainException('Invalid table name');
        }

        $this->activeTable = $activeTable;
        $this->updateState();
    }

    public function deactivate()
    {
        $this->activeTable = null;
        $this->updateState();
    }

    public function getTarget()
    {
        $tables = $this->tables;

        if ($this->activeTable !== null) {
            unset($tables[$this->activeTable]);
        }

        reset($tables);

        return key($tables);
    }

    public function updateState()
    {
        $this->state->update(self::STATE_VARIABLE, $this->activeTable);
    }

    private function isValidTable($table)
    {
        return isset($this->tables[$table]);
    }
}
