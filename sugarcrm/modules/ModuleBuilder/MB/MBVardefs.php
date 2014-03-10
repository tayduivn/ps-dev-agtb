<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
class MBVardefs{
	var $templates = array();
	var $iTemplates = array();
	var $vardefs = array();
	var $vardef = array();
	var $path = '';
	var $name = '';
	var $errors = array();

	function MBVardefs($name, $path, $key_name){
		$this->path = $path;
		$this->name = $name;
		$this->key_name = $key_name;
		$this->load();
	}

	function loadTemplate($by_group, $template, $file){
		$module = $this->name;
		$table_name = $this->name;
		$object_name = $this->key_name;
		$_object_name = strtolower($this->key_name);

		// required by the vardef template for team security in SugarObjects
		$table_name = strtolower($module);

		if(file_exists($file)){
			include($file);
            if (isset($vardefs))
            {
                if($by_group){
                    $this->vardefs['fields'] [$template]= $vardefs['fields'];
                }else{
                    $this->vardefs['fields']= array_merge($this->vardefs['fields'], $vardefs['fields']);
                    if(!empty($vardefs['relationships'])){
                        $this->vardefs['relationships']= array_merge($this->vardefs['relationships'], $vardefs['relationships']);
                    }
                }
            }
		}
        //Bug40450 - Extra 'Name' field in a File type module in module builder
        if(array_key_exists('file', $this->templates))
        {
            unset($this->vardefs['fields']['name']);
            unset($this->vardefs['fields']['file']['name']);
        }

	}

	function mergeVardefs($by_group=false){
		$this->vardefs = array(
					'fields'=>array(),
					'relationships'=>array(),
		);
//		$object_name = $this->key_name;
//		$_object_name = strtolower($this->name);
		$module_name = $this->name;
		$this->loadTemplate($by_group,'basic',  MB_TEMPLATES . '/basic/vardefs.php');
		foreach($this->iTemplates as $template=>$val){
			$file = MB_IMPLEMENTS . '/' . $template . '/vardefs.php';
			$this->loadTemplate($by_group,$template, $file);
		}
		foreach($this->templates as $template=>$val){
			if($template == 'basic')continue;
			$file = MB_TEMPLATES . '/' . $template . '/vardefs.php';
			$this->loadTemplate($by_group,$template, $file);
		}

		if($by_group){
			$this->vardefs['fields'][$this->name] = $this->vardef['fields'];
		}else{
			$this->vardefs['fields'] = array_merge($this->vardefs['fields'], $this->vardef['fields']);
		}
	}

	function updateVardefs($by_group=false){
		$this->mergeVardefs($by_group);
	}


	function getVardefs(){
		return $this->vardefs;
	}

	function getVardef(){
		return $this->vardef;
	}

	/**
	 * Ensure the vardef name is OK for database
	 * @param string $name
	 * @return string
	 */
	protected function validateVardefName($name)
	{
	    $name = $GLOBALS['db']->getValidDBName($name, true, 'column');
	    if(!empty($this->reserved_words[$name])) {
	        $name = $name."_field";
	    }
	    return $GLOBALS['db']->getValidDBName($name, true, 'column');
	}

    function addFieldVardef($vardef)
    {
        if(!isset($vardef['default']) || strlen($vardef['default']) == 0)
        {
            unset($vardef['default']);
        }
        if(empty($this->vardef['fields'][$vardef['name']])) {
            // clean up names for new fields
            $vardef['name'] = $this->validateVardefName($vardef['name']);
        }
        $this->vardef['fields'][$vardef['name']] = $vardef;
    }

	function deleteField($field){
		unset($this->vardef['fields'][$field->name]);
	}

	function save(){
		$header = file_get_contents('modules/ModuleBuilder/MB/header.php');
		write_array_to_file('vardefs', $this->vardef, $this->path . '/vardefs.php','w', $header);
	}

	function build($path){
		$header = file_get_contents('modules/ModuleBuilder/MB/header.php');
		write_array_to_file('dictionary["' . $this->name . '"]', $this->getVardefs(), $path . '/vardefs.php', 'w', $header);
	}
	function load(){
		$this->vardef = array('fields'=>array(), 'relationships'=>array());
		if(file_exists($this->path . '/vardefs.php')){
			include($this->path. '/vardefs.php');
			$this->vardef = $vardefs;
		}
	}

	/**
	 * List of SQL reserved words
	 * Column can not be named as one of these
	 * @var array
	 */
	protected $reserved_words = array("access" => true, "accessible" => true, "add" => true,
        "after" => true, "all" => true, "allocate" => true, "allow" => true, "alter" => true,
        "analyze" => true, "and" => true, "any" => true, "as" => true, "asc" => true,
        "asensitive" => true, "associate" => true, "asutime" => true, "at" => true, "audit" => true,
        "aux" => true, "auxiliary" => true, "before" => true, "begin" => true, "between" => true,
        "big" => true, "bigint" => true, "binary" => true, "bind" => true, "binlog" => true,
        "blob" => true, "both" => true, "bufferpool" => true, "by" => true, "calc" => true,
        "call" => true, "capture" => true, "cascade" => true, "cascaded" => true, "case" => true,
        "cast" => true, "ccsid" => true, "ceiling" => true, "cert" => true, "change" => true,
        "char" => true, "character" => true, "check" => true, "clone" => true, "close" => true,
        "cluster" => true, "collate" => true, "collection" => true, "collid" => true,
        "column" => true, "comment" => true, "commit" => true, "compress" => true, "concat" => true,
        "condition" => true, "connect" => true, "connection" => true, "constraint" => true,
        "contains" => true, "content" => true, "continue" => true, "convert" => true,
        "create" => true, "cross" => true, "ctype" => true, "current" => true, "currval" => true,
        "cursor" => true, "data" => true, "database" => true, "databases" => true, "date" => true,
        "day" => true, "days" => true, "dbinfo" => true, "dec" => true, "decimal" => true,
        "declare" => true, "default" => true, "delayed" => true, "delete" => true, "desc" => true,
        "describe" => true, "descriptor" => true, "deterministic" => true, "disable" => true,
        "disallow" => true, "distinct" => true, "distinctrow" => true, "div" => true, "do" => true,
        "document" => true, "double" => true, "down" => true, "drop" => true, "dssize" => true,
        "dual" => true, "dynamic" => true, "each" => true, "editproc" => true, "else" => true,
        "elseif" => true, "enclosed" => true, "encoding" => true, "encryption" => true,
        "end" => true, "ending" => true, "erase" => true, "escape" => true, "escaped" => true,
        "even" => true, "except" => true, "exception" => true, "exclusive" => true, "exec" => true,
        "execute" => true, "exists" => true, "exit" => true, "explain" => true, "external" => true,
        "false" => true, "fenced" => true, "fetch" => true, "fieldproc" => true, "file" => true,
        "final" => true, "first" => true, "float" => true, "floor" => true, "for" => true,
        "force" => true, "foreign" => true, "found" => true, "free" => true, "from" => true,
        "full" => true, "fulltext" => true, "function" => true, "generated" => true, "get" => true,
        "global" => true, "go" => true, "goto" => true, "grant" => true, "group" => true,
        "gtids" => true, "half" => true, "handler" => true, "having" => true, "high" => true,
        "hold" => true, "hour" => true, "hours" => true, "identified" => true, "if" => true,
        "ignore" => true, "immediate" => true, "in" => true, "inclusive" => true,
        "increment" => true, "index" => true, "infile" => true, "inherit" => true, "initial" => true,
        "inner" => true, "inout" => true, "insensitive" => true, "insert" => true, "int" => true,
        "integer" => true, "intersect" => true, "interval" => true, "into" => true, "io" => true,
        "is" => true, "isobid" => true, "iterate" => true, "jar" => true, "join" => true,
        "keep" => true, "key" => true, "keys" => true, "kill" => true, "label" => true,
        "language" => true, "last" => true, "lc" => true, "leading" => true, "leave" => true,
        "left" => true, "level" => true, "like" => true, "limit" => true, "linear" => true,
        "lines" => true, "load" => true, "local" => true, "locale" => true, "localtime" => true,
        "localtimestamp" => true, "locator" => true, "locators" => true, "lock" => true,
        "lockmax" => true, "locksize" => true, "long" => true, "longblob" => true,
        "longtext" => true, "loop" => true, "low" => true, "maintained" => true, "master" => true,
        "match" => true, "materialized" => true, "maxextents" => true, "maxvalue" => true,
        "mediumblob" => true, "mediumint" => true, "mediumtext" => true, "microsecond" => true,
        "microseconds" => true, "middleint" => true, "minus" => true, "minute" => true,
        "minutes" => true, "mlslabel" => true, "mod" => true, "mode" => true, "modifies" => true,
        "modify" => true, "month" => true, "months" => true, "natural" => true, "next" => true,
        "nextval" => true, "no" => true, "noaudit" => true, "nocompress" => true,
        "nonblocking" => true, "none" => true, "not" => true, "nowait" => true, "null" => true,
        "nulls" => true, "number" => true, "numeric" => true, "numparts" => true, "obid" => true,
        "of" => true, "offline" => true, "old" => true, "on" => true, "online" => true,
        "open" => true, "optimization" => true, "optimize" => true, "option" => true,
        "optionally" => true, "or" => true, "order" => true, "organization" => true, "out" => true,
        "outer" => true, "outfile" => true, "package" => true, "padded" => true, "parameter" => true,
        "part" => true, "partition" => true, "partitioned" => true, "partitioning" => true,
        "path" => true, "pctfree" => true, "period" => true, "piecesize" => true, "plan" => true,
        "precision" => true, "prepare" => true, "prevval" => true, "primary" => true,
        "prior" => true, "priority" => true, "priqty" => true, "privileges" => true,
        "procedure" => true, "program" => true, "psid" => true, "public" => true, "purge" => true,
        "query" => true, "queryno" => true, "range" => true, "raw" => true, "read" => true,
        "reads" => true, "real" => true, "references" => true, "refresh" => true, "regexp" => true,
        "release" => true, "rename" => true, "repeat" => true, "replace" => true, "require" => true,
        "resignal" => true, "resource" => true, "restrict" => true, "result" => true,
        "return" => true, "returns" => true, "revoke" => true, "right" => true, "rlike" => true,
        "role" => true, "rollback" => true, "round" => true, "row" => true, "rowid" => true,
        "rownum" => true, "rows" => true, "rowset" => true, "run" => true, "savepoint" => true,
        "schema" => true, "schemas" => true, "scratchpad" => true, "second" => true,
        "seconds" => true, "secqty" => true, "security" => true, "select" => true,
        "sensitive" => true, "separator" => true, "sequence" => true, "server" => true,
        "session" => true, "set" => true, "share" => true, "show" => true, "signal" => true,
        "simple" => true, "size" => true, "small" => true, "smallint" => true, "some" => true,
        "source" => true, "spatial" => true, "specific" => true, "sql" => true,
        "sqlexception" => true, "sqlstate" => true, "sqlwarning" => true, "ssl" => true,
        "standard" => true, "start" => true, "starting" => true, "statement" => true,
        "static" => true, "stay" => true, "stogroup" => true, "stores" => true, "straight" => true,
        "style" => true, "successful" => true, "summary" => true, "synonym" => true,
        "sysdate" => true, "systimestamp" => true, "table" => true, "tablespace" => true,
        "terminated" => true, "then" => true, "time" => true, "timestamp" => true,
        "tinyblob" => true, "tinyint" => true, "tinytext" => true, "to" => true, "trailing" => true,
        "trigger" => true, "true" => true, "truncate" => true, "type" => true, "uid" => true,
        "undo" => true, "union" => true, "unique" => true, "unlock" => true, "unsigned" => true,
        "until" => true, "up" => true, "update" => true, "usage" => true, "use" => true,
        "user" => true, "using" => true, "utc" => true, "validate" => true, "validproc" => true,
        "value" => true, "values" => true, "varbinary" => true, "varchar" => true,
        "varcharacter" => true, "variable" => true, "variant" => true, "varying" => true,
        "vcat" => true, "verify" => true, "view" => true, "volatile" => true, "volumes" => true,
        "when" => true, "whenever" => true, "where" => true, "while" => true, "with" => true,
        "wlm" => true, "write" => true, "xmlcast" => true, "xmlexists" => true,
        "xmlnamespaces" => true, "xor" => true, "year" => true, "years" => true,
        "zerofilladd" => true, "zone" => true,
	);
}
