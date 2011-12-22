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

class SugarSearchEngineElastic implements SugarSearchEngineInterface
{
    private $_server = "";
    private $_config = array();
    private $_client = null;
    private $_indexName = "";
    
    public function __construct($params)
    {
        $this->_config = $params;

        //TODO: Support basic auth?
        $scheme = isset($this->_config['scheme']) ? $this->_config['scheme'] : 'http';
        $port = isset($this->_config['port']) ? $this->_config['port'] : '9200';
        $host = isset($this->_config['host']) ? $this->_config['host'] : 'localhost';
        $index = isset($this->_config['index']) ? $this->_config['index'] : ($GLOBALS['sugar_config']['unique_key']);
        $this->_server = "{$scheme}://{$host}:$port/$index";
        $this->_indexName = $GLOBALS['sugar_config']['unique_key'];
        spl_autoload_register(array($this, 'loader'));

        $this->_client = new Elastica_Client();
    }

    public function connect()
    {

    }

    public function indexBean($bean, $batch = TRUE)
    {
        $GLOBALS['log']->fatal("GOING TO INDEX BEAN");
        if($batch)
            $this->indexSingleBean($bean);

    }

    protected function indexSingleBean($bean)
    {
        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $type = new Elastica_Type($index, 'SugarBean');
            $doc = new Elastica_Document($bean->id, array('name' => $bean->name));
            $type->addDocument($doc);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to index bean with error: {$e->getMessage()}");
        }

    }

    public function flush()
    {

    }

    public function delete($bean)
    {

    }

    public function search($query, $offset = 0, $limit = 20)
    {
        $GLOBALS['log']->fatal("Going to search with query $query");
        $results = array();
        try
        {
            $s = new Elastica_Search($this->_client);
            $results = $s->search($query);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to perform search with error: {$e->getMessage()}");
        }
        $GLOBALS['log']->fatal("finished searching with results " . var_export($results, TRUE));
        return $results;
    }

    protected function loader($className)
    {
        $fileName = str_replace('_', '/', $className);
        $path = 'include/SugarSearchEngine/Elastic/' . $fileName . '.php';
        if( file_exists($path) )
            require_once($path);
        else
            return FALSE;
    }
}