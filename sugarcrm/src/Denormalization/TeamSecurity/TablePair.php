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

namespace Sugarcrm\Sugarcrm\Denormalization\TeamSecurity;

use DomainException;

final class TablePair
{
    const STATE_VARIABLE = 'active_table';

    /**#@+
     * @var string
     */
    private $table1;
    private $table2;
    /**#@-*/

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
        $this->table1 = $table1;
        $this->table2 = $table2;

        $this->state = $state;

        $activeTable = $this->state->get(self::STATE_VARIABLE);

        if ($activeTable !== null && $this->isValidTable($activeTable)) {
            $this->activeTable = $activeTable;
        }
    }

    public function getActiveTable()
    {
        return $this->activeTable;
    }

    public function activate($table)
    {
        if (!$this->isValidTable($table)) {
            throw new DomainException('Invalid table name');
        }

        $this->activeTable = $table;
        $this->updateState();
    }

    public function deactivate()
    {
        $this->activeTable = null;
        $this->updateState();
    }

    public function getTargetTable()
    {
        if ($this->activeTable === $this->table1) {
            return $this->table2;
        }

        return $this->table1;
    }

    public function updateState()
    {
        $this->state->update(self::STATE_VARIABLE, $this->activeTable);
    }

    private function isValidTable($table)
    {
        return $table === $this->table1
            || $table === $this->table2;
    }
}
