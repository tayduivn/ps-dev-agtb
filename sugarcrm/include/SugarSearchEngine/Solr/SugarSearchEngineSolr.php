<?php
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
        $instance = SugarSearchEngine::getInstance();
        
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
        $this->_backend->optimize();
    }

    public function delete($bean)
    {
        $this->_backend->deleteById($bean->id);
        $this->_backend->optimize();
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

    public function useEngine()
    {
        return true;
    }
}