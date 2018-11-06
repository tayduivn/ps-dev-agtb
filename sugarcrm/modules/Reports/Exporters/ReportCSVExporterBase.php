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
declare(strict_types=1);
namespace Sugarcrm\Sugarcrm\modules\Reports\Exporters;

require_once 'include/export_utils.php'; // for user defined delimiter

/**
 * Class ReportCSVExporterBase
 * @package Sugarcrm\Sugarcrm\modules\Reports\Exporters
 */
abstract class ReportCSVExporterBase implements ReportExporterInterface
{
    /**
     * @var \Report
     */
    protected $reporter;

    public function __construct(\Report $reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * Concrete implementations must define their own export methodology
     *
     * @return string
     */
    abstract public function export(): string;

    /**
     * Run the corresponding query to get the data
     * Concrete implementations must define their own query method
     */
    abstract protected function runQuery();

    /**
     * To prepare the export
     */
    protected function prepareExport()
    {
        $this->reporter->do_export = true;
        $this->reporter->_load_currency();
        $this->runQuery();
    }

    /**
     * Helper function that builds the grand total csv
     * @return string
     */
    protected function getGrandTotal()
    {
        $return = '"Grand Total"' . $this->getLineEnd();
        $return .= '"' . implode($this->getDelimiter(), $this->reporter->get_total_header_row(true)) . '"' . $this->getLineEnd();

        $row = $this->reporter->get_summary_total_row();
        if (isset($row['cells'])) {
            $return .= '"' . implode($this->getDelimiter(), $row['cells']) . '"';
        }

        return $return;
    }

    /**
     * Wrapper of global getDelimiter
     * @return string
     */
    protected function getDelimiter()
    {
        return '"' . getDelimiter() . '"';
    }

    /**
     * @param int $count
     * @return string
     */
    protected function getLineEnd(int $count = 1)
    {
        $ret = '';
        $c = 0;
        while ($c < $count) {
            $ret .= "\r\n";
            $c++;
        }
        return $ret;
    }
}
