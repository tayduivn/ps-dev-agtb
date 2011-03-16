<?php


require_once("data/Relationships/SugarRelationship.php");

class SugarRelationshipFactory {
    static $rfInstance;

    protected $relationships;

    protected function __construct(){
        //Load the relationship definitions from the cache.
        $this->loadRelationships();
    }

    public static function getInstance()
    {
        if (is_null(self::$rfInstance))
            self::$rfInstance = new SugarRelationshipFactory();
        return self::$rfInstance;
    }

    /**
     * @param  $relationshipName String name of relationship to load
     * @return void
     *
     *
     * 
     */
    public function getRelationship($relationshipName)
    {
        if (empty($this->relationships[$relationshipName])) return false;

        $def = $this->relationships[$relationshipName];

        $type = isset($def['true_relationship_type']) ? $def['true_relationship_type'] : $def['relationship_type'];
        switch($type)
        {
            case "many-to-many":
                require_once("data/Relationships/M2MRelationship.php");
                return new M2MRelationship($def);
            break;
            case "one-to-many":
                require_once("data/Relationships/One2MBeanRelationship.php");
                if (empty($def['true_relationship_type'])){
                    return new One2MBeanRelationship($def);
                }
                else {
                    return new One2MRelationship($def);
                }
                break;
            case "one-to-one":
                require_once("data/Relationships/One2OneRelationship.php");
                return new One2OneRelationship($def);
                break;
        }

        return false;
    }

    protected function loadRelationships()
    {
        if(sugar_is_file($this->getCacheFile()))
        {
            include($this->getCacheFile());
            $this->relationships = $relationships;
        } else {
            $this->buildRelationshipCache();
        }
    }

    protected function buildRelationshipCache()
    {
        global $beanList, $dictionary;
        include_once("modules/TableDictionary.php");

        //Reload ALL the module vardefs....
        foreach($beanList as $moduleName => $beanName)
        {
            VardefManager::loadVardef($moduleName, $beanName);
        }

        $relationships = array();

        //Grab all the relationships from the dictionary.
        foreach ($dictionary as $key => $def)
        {
            if (!empty($def['relationships']))
            {
                foreach($def['relationships'] as $relKey => $relDef)
                {
                    if ($key == $relKey) //Relationship only entry, we need to capture everything
                        $relationships[$key] = array_merge(array('name' => $key), $def, $relDef);
                    else
                        $relationships[$relKey] = array_merge(array('name' => $relKey), $relDef);
                }
            }
        }
        //Save it out
        sugar_mkdir(dirname($this->getCacheFile()), null, true);
        $out="<?php \n \$relationships=" . var_export($relationships, true) .";";
        sugar_file_put_contents($this->getCacheFile(), $out);

        $this->relationships = $relationships;
    }

	protected function getCacheFile() {
		return "{$GLOBALS['sugar_config']['cache_dir']}Relationships/relationships.cache.php";
	}



}