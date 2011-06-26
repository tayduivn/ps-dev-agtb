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
/*********************************************************************************
 * $Id: view.step1.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: view handler for step 1 of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Import/views/ImportView.php');
require_once('modules/Import/ImportFile.php');
require_once('modules/Import/ImportFileSplitter.php');
require_once('modules/Import/ImportCacheFiles.php');
require_once('modules/Import/ImportDuplicateCheck.php');

require_once('include/upload_file.php');

class ImportViewExtimport extends ImportView
{
    protected $pageTitleKey = 'LBL_STEP_DUP_TITLE';
    protected $adapter = FALSE;
    protected $externalSource = '';
    protected $offset = 0;
    protected $recordsPerImport = 10;

    public function __construct($bean = null, $view_object_map = array())
    {
        parent::__construct($bean, $view_object_map);
        $this->externalSource = isset($_REQUEST['external_source']) ? $_REQUEST['external_source'] : '';
        $this->offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : '0';
        $this->recordsPerImport = !empty($_REQUEST['records_per_import']) ? $_REQUEST['records_per_import'] : '';
        $this->adapter = $this->getExternalSourceAdapter();
        $GLOBALS['log']->fatal("Initiating external source import- source:{$this->externalSource}, offset: {$this->offset}, recordsPerImport: {$this->recordsPerImport}");
    }
 	/**
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $app_strings, $current_user;
        global $sugar_config;

        if($this->adapter === FALSE)
        {
            $GLOBALS['log']->fatal("Found invalid adapter");
            $resp = array('totalCount' => -1, 'done' => TRUE);
            echo json_encode($resp);
            sugar_cleanup(TRUE);
        }

        $columncount = isset($_REQUEST['columncount']) ? $_REQUEST['columncount'] : '';
        $userMapping = $this->getUserMapping($columncount);

        try
        {
            $recordSet = $this->adapter->getRecordSet($this->offset, $this->recordsPerImport);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to import external feed, exception: " . $e->getMessage() );
            $resp = array('totalCount' => -1, 'done' => TRUE);
            echo json_encode($resp);
            sugar_cleanup(TRUE);
        }

        //Begin our import
        $this->importRecordSet($recordSet['data'], $userMapping);


        //Send back our results.
        $result = $recordSet['meta'];
        echo json_encode($result);
        sugar_cleanup(TRUE);
    }

    protected function importRecordSet($rows, $userMapping)
    {
        $GLOBALS['log']->fatal("Importing into module: {$this->importModule}");


    }

    protected function getExternalSourceAdapter()
    {
        $externalSourceName = ucfirst($this->externalSource);
        $externalSourceClassName = "ExternalSource{$externalSourceName}Adapter";
        $externalSourceFile = "modules/Import/adapters/{$externalSourceClassName}.php";
        if( file_exists("custom/" . $externalSourceFile) )
        {
            require_once("custom/" . $externalSourceFile);
        }
        else if( file_exists($externalSourceFile) )
        {
            require_once($externalSourceFile);
        }
        else
        {
            $GLOBALS['log']->fatal("Unable to load external source adapter.");
            return FALSE;
        }

        if( class_exists($externalSourceClassName) )
        {
            $GLOBALS['log']->fatal("RETURNING EXTENRAL SOURCE CLASS");
            return new $externalSourceClassName();
        }
        else
        {
            $GLOBALS['log']->fatal("Unable to load external source adapter class.");
            return FALSE;
        }
    }
    /**
     * Return the user mapping that was constructed during the first page of import.
     *
     * @param  $columncount
     * @return array
     */
    protected function getUserMapping($columncount)
    {
        $userMapping = array();
        for($i=0;$i<$columncount;$i++)
        {
            $sugarKeyIndex = 'colnum_' . $i;
            $extKeyIndex = 'extkey_' . $i;
            $sugarKey = $_REQUEST[$sugarKeyIndex];
            //User specified don't map, keep going.
            if($sugarKey == -1)
                continue;

            $extKey = $_REQUEST[$extKeyIndex];
            $defaultValue = $_REQUEST[$sugarKey];
            $userMapping[] = array('sugar_key'=> $sugarKey, 'ext_key' => $extKey, 'default_value' => $defaultValue);
        }
        
        return $userMapping;
    }
}