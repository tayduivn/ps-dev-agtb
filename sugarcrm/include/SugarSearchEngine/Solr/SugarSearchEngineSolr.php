<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

require_once('include/SugarSearchEngine/Interface.php');
require_once('include/SugarSearchEngine/Solr/PHPSolr/Service.php');

class SugarSearchEngineSolr implements SugarSearchEngineInterface
{
    private $_backend;
    protected $_documents = array();
    
    public function __construct()
    {
    }

    public function connect($config)
    {
        $this->_backend = new Apache_Solr_Service($config['host'], $config['port'], $config['path']);
    }

    public function indexBean($bean)
    {
        //TODO: Needs to be re-implemented to pickup correct fields.
        $document = new Apache_Solr_Document();
        $document->addField('category', $bean->module_name);
        $document->addField('id', $bean->id);
        $document->addField('description', $bean->description);
        $document->addField('name', $bean->name);
        $this->_backend->addDocument($document);
    }

    public function flush()
    {
        $this->_backend->commit();

        //TODO: Optimizations should probably not be called here.
        //$this->_backend->optimize();
    }

    public function delete($bean)
    {
        $this->_backend->deleteById($bean->id);
    }

    public function search($query, $offset = 0, $limit = 20)
    {
        $results = $this->_backend->search($query, $offset, $limit);
        $output = array();
        foreach ($results->response->docs as $doc)
        {
            $docResult = array();
            foreach ($doc as $field => $value)
            {
                $docResult[$field] = $value;
            }
            $output[] = $docResult;
        }
        return $output;
    }
}