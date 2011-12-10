<?php

require_once 'include/php-sql-parser.php';

/**
 * SQL Validator class
 * @api
 */
class SugarSQLValidate
{
	/**
	 * Parse SQL query WHERE and ORDER BY clauses and validate that nothing bad is happening there
	 * @param string $where
	 * @param string $order_by
	 * @return bool
	 */
	public function validateQueryClauses($where, $order_by = '')
	{
	    if(empty($where) && empty($order_by)) {
	        return true;
	    }

	    if(empty($where) && !empty($order_by)) {
	        $where = "deleted=0";
	    }

		$parser = new PHPSQLParser();
		$testquery = "SELECT dummy FROM dummytable WHERE $where";
		$clauses = 3;
		if(!empty($order_by)) {
		    $testquery .= " ORDER BY $order_by";
		    $clauses++;
		}
		$parsed = $parser->parse($testquery);
		//$GLOBALS['log']->debug("PARSE: ".var_export($parsed, true));

		if(count($parsed) != $clauses) {
		    // we assume: SELECT, FROM, WHERE, maybe ORDER
		    return false;
		}
		$parts = array_keys($parsed);
		if($parts[0] != "SELECT" || $parts[1] != "FROM" || $parts[2] != "WHERE") {
		    // check the keys to be SELECT, FROM, WHERE
		    return false;
		}
		if(!empty($order_by) && $parts[3] != "ORDER") {
		    // extra key is ORDER
		    return false;
		}
        // verify SELECT didn't change
        if(count($parsed["SELECT"]) != 1 || $parsed["SELECT"][0] !== array ('expr_type' => 'colref','alias' => '`dummy`', 'base_expr' => 'dummy', 'sub_tree' => false)) {
            $GLOBALS['log']->debug("validation failed SELECT");
            return false;
        }
        // verify FROM didn't change
        if(count($parsed["FROM"]) != 1 || $parsed["FROM"][0] !== array ('table' => 'dummytable', 'alias' => 'dummytable', 'join_type' => 'JOIN', 'ref_type' => '', 'ref_clause' => '', 'base_expr' => false, 'sub_tree' => false)) {
            $GLOBALS['log']->debug("validation failed FROM");
            return false;
        }
        // check WHERE
        if(!$this->validateExpression($parsed["WHERE"])) {
            $GLOBALS['log']->debug("validation failed WHERE");
            return false;
        }
        // check ORDER
        if(!empty($order_by) && !$this->validateExpression($parsed["ORDER"])) {
            $GLOBALS['log']->debug("validation failed ORDER");
            return false;
        }
		return true;
	}

	/**
	 * Prohibited functions
	 * @var array
	 */
	protected $bad_functions = array("benchmark", "encode", "sleep",
	"generate_series", "load_file", "sys_eval", "user_name",
	"xp_cmdshell", "sys_exec", "sp_replwritetovarbin");

	/**
	 * Validate parsed SQL expression
	 * @param array $expr Parsed expression
	 * @return bool
	 */
	protected function validateExpression($expr)
	{
	    foreach($expr as $term) {
	        // check subtrees
	        if(!empty($term['sub_tree']) && !$this->validateExpression($term['sub_tree'])) {
	            return false;
	        }
	        if(isset($term['type']) && $term['type'] == 'expression') {
	            continue;
	        }
	        if($term['expr_type'] == 'const') {
	            // constants are OK
	            continue;
	        }
	        if($term['expr_type'] == 'subquery') {
	            // subqueries are verboten
	            $GLOBALS['log']->debug("validation failed subquery");
	            return false;
	        }
	        if($term['expr_type'] == 'function') {
	            // prohibit some functions
	            if(in_array(strtolower($term['base_expr']), $this->bad_functions)) {
	                $GLOBALS['log']->debug("validation failed function");
	                return false;
	            }
	        }
	        if($term['expr_type'] == 'colref' && !$this->validateColumnName($term['base_expr'])) {
	            // check column names
	            $GLOBALS['log']->debug("validation failed column name");
	            return false;
	        }
	        if(!empty($term['alias']) && $term['alias'] != $term['base_expr'] && $term['alias'] != "`".$term['base_expr']."`") {
	            $GLOBALS['log']->debug("validation failed alias");
	            return false;
	        }
	    }
	    return true;
	}

	/**
	 * Validate column name
	 * @param string $name
	 * @return bool
	 */
	protected function validateColumnName($name)
	{
	    if($name == ",") return true; // sometimes , gets as column name
	    $name = strtolower($name); // case does not matter
	    if(preg_match("/[^a-z0-9._]/", $name)) {
	        // bad chars in name
	        return false;
	    }
	    $parts = explode(".", $name);
	    if(count($parts) > 2) {
	        // too many dots
	        return false;
	    }
	    if($parts[0] == "user_hash" || (!empty($parts[1]) && $parts[1] == "user_hash")) {
	        // this column is verboten
	        return false;
	    }
	    return true;
	}
}