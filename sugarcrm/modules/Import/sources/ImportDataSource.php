<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Import/ImportCacheFiles.php');



abstract class ImportDataSource implements Iterator
{
    /**
     * Count of rows processed
     */
    protected $_rowsCount = 0;

    /**
     * True if the current row has already had an error it in, so we don't increase the $_errorCount
     */
    protected $_rowCountedForErrors = false;

    /**
     * Count of rows with errors
     */
    private $_errorCount = 0;

    /**
     * Count of duplicate rows
     */
    private $_dupeCount = 0;

    /**
     * Count of newly created rows
     */
    private $_createdCount = 0;

    /**
     * Count of updated rows
     */
    private $_updatedCount = 0;

    /**
     * Sourcename used as an identifier for this import
     */
    protected $_sourcename;

    /**
     * Array of the values in the current array we are in
     */
    protected $_currentRow = FALSE;

    /**
     * Holds any locale settings needed for import.  These can be provided by the user
     * or explicitly set by the user.
     */
    protected $_localeSettings = array();


    /**
     * Add this row to the UsersLastImport table
     *
     * @param string $import_module name of the module we are doing the import into
     * @param string $module        name of the bean we are creating for this import
     * @param string $id            id of the recorded created in the $module
     */
    public static function writeRowToLastImport($import_module, $module, $id)
    {
        // cache $last_import instance
        static $last_import;

        if ( !($last_import instanceof UsersLastImport) )
            $last_import = new UsersLastImport();

        $last_import->id = null;
        $last_import->deleted = null;
        $last_import->assigned_user_id = $GLOBALS['current_user']->id;
        $last_import->import_module = $import_module;
        //BEGIN SUGARCRM flav!=sales ONLY
        if ( $module == 'Case' ) {
            $module = 'aCase';
        }
        //END SUGARCRM flav!=sales ONLY
        $last_import->bean_type = $module;
        $last_import->bean_id = $id;
        return $last_import->save();
    }


    /**
     * Writes the row out to the ImportCacheFiles::getErrorFileName() file
     *
     * @param $error string
     * @param $fieldName string
     * @param $fieldValue mixed
     */
    public function writeError($error, $fieldName, $fieldValue)
    {
        $fp = sugar_fopen(ImportCacheFiles::getErrorFileName(),'a');
        fputcsv($fp,array($error,$fieldName,$fieldValue,$this->_rowsCount));
        fclose($fp);

        if ( !$this->_rowCountedForErrors )
        {
            $this->_errorCount++;
            $this->_rowCountedForErrors = true;
            $this->writeErrorRecord();
        }
    }


    /**
     * Writes the totals and filename out to the ImportCacheFiles::getStatusFileName() file
     */
    public function writeStatus()
    {
        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(),'a');
        $statusData = array($this->_rowsCount,$this->_errorCount,$this->_dupeCount,
                            $this->_createdCount,$this->_updatedCount,$this->_sourcename);
        fputcsv($fp, $statusData);
        fclose($fp);
    }

    /**
     * Writes the row out to the ImportCacheFiles::getDuplicateFileName() file
     */
    public function markRowAsDuplicate()
    {
        $fp = sugar_fopen(ImportCacheFiles::getDuplicateFileName(),'a');
        fputcsv($fp, $this->_currentRow);
        fclose($fp);

        $this->_dupeCount++;
    }

    /**
     * Marks whether this row created a new record or not
     *
     * @param $createdRecord bool true if record is created, false if it is just updated
     */
    public function markRowAsImported($createdRecord = true)
    {
        if ( $createdRecord )
            ++$this->_createdCount;
        else
            ++$this->_updatedCount;
    }

    /**
     * Writes the row out to the ImportCacheFiles::getErrorRecordsFileName() file
     */
    public function writeErrorRecord()
    {
        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsFileName(),'a');
        fputcsv($fp, !$this->_currentRow ? array() : $this->_currentRow);
        fclose($fp);
    }

    public function __get($var)
    {
        if( isset($_REQUEST[$var]) )
            return $_REQUEST[$var];
        else if( isset($this->_localeSettings[$var]) )
            return $this->_localeSettings[$var];
        else
            return $this->$var;
    }
    
}
 
