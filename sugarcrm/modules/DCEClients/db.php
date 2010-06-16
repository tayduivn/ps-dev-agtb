<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
  require_once('client_utils.php');

class DB {


	var $database = "";
	var $link_id = 0;
	var $query_id = 0;
	var $record = array ();
	var $errdesc = "";
	var $errno = 0;
	var $reporterror = 1;
	var $server = '';
	var $user = '';
	var $password = '';

	function connect($new = false) {
        actionLog("<p> ".$this->server ." <p> ". $this->user ." <p> ".$this->password);
		if (0 == $this->link_id) {
			$this->link_id = mysql_connect($this->server, $this->user, $this->password, $new );
		}else{
            $this->close();
            $this->link_id = mysql_connect($this->server, $this->user, $this->password, $new );
        }
		if (!$this->link_id) {
			$this->halt("Link-ID == false, connect failed");
		}
		if ($this->database != "") {
			if (!mysql_select_db($this->database, $this->link_id)) {
				$this->halt("cannot use database ".$this->database);
			}
		}
	}

	function geterrdesc() {
		$this->error = mysql_error();
		return $this->error;
	}

	function geterrno() {
		$this->errno = mysql_errno();
		return $this->errno;
	}

	function select_db($database = "") {
		if ($database != "") {
			$this->database = $database;
		}

		if (!mysql_select_db($this->database, $this->link_id)) {
			$this->halt("cannot use database ".$this->database);
		}

	}

	function query($query_string) {
		global $query_count, $showqueries, $explain, $querytime;

		$this->query_id = mysql_query($query_string, $this->link_id);
		if (!$this->query_id) {
			$this->halt("Invalid SQL: ".$query_string);
		}

		$query_count ++;

		return $this->query_id;
	}

	function fetch_array($query_id = -1, $query_string = "") {
			// retrieve one row
	if ($query_id != -1) {
			$this->query_id = $query_id;
		}

		if (isset ($this->query_id)) {
			$this->record = mysql_fetch_array($this->query_id);
		} else {
			if (!empty ($query_string)) {
				$this->halt("Invalid query id (".$this->query_id.") on this query: $query_string");
			} else {
				$this->halt("Invalid query id ".$this->query_id." specified");
			}
		}

		return $this->record;
	}

	function free_result($query_id = -1) {
		if ($query_id != -1) {
			$this->query_id = $query_id;
		}
		return @ mysql_free_result($this->query_id);
	}

	function query_first($query_string) {
		$query_id = $this->query($query_string);
		$returnarray = $this->fetch_array($query_id, $query_string);
		$this->free_result($query_id);
		return $returnarray;
	}

	function data_seek($pos, $query_id = -1) {
		if ($query_id != -1) {
			$this->query_id = $query_id;
		}
		return mysql_data_seek($this->query_id, $pos);
	}

	function num_rows($query_id = -1) {
		if ($query_id != -1) {
			$this->query_id = $query_id;
		}
		return mysql_num_rows($this->query_id);
	}

	function num_fields($query_id = -1) {
		if ($query_id != -1) {
			$this->query_id = $query_id;
		}
		return mysql_num_fields($this->query_id);
	}

	function affectedrows() {
		return mysql_affected_rows($this->link_id);
	}

	function insert_id() {
		return mysql_insert_id($this->link_id);
	}

	function close() {
		return mysql_close($this->link_id);
	}

	function halt($msg) {
		$this->errdesc = mysql_error();
		$this->errno = mysql_errno();
		// prints warning message when there is an error
		global $technicalemail, $bbuserinfo, $scriptpath;

		if ($this->reporterror == 1) {
			$message = "Database error:";
			$message .= "mysql error: $this->errdesc\n\n";
			$message .= "mysql error number: $this->errno\n\n";
			$message .= "Date: ".date("l dS of F Y h:i:s A")."\n";
			$message .= "Script: ". (($scriptpath) ? $scriptpath : getenv("REQUEST_URI"))."\n";
			$message .= "Referer: ".getenv("HTTP_REFERER")."\n";

			if ($technicalemail) {
				@ mail($technicalemail, "$this->appshortname Database error!", $message, "From: $technicalemail");
			}

            actionLog($message);
            echo "$message ";
			exit;
		}
	}
    
    
    function escape ( $str, $magic_quotes = false )
    {
            switch ( gettype ( $str ) )
            {
                case 'string'   :
                    $replaceQuote = "\\'";  /// string to use to replace quotes
                    if ( ! $magic_quotes ) {
    
                        if ( $replaceQuote [ 0 ] == '\\' ){
                            $str = str_replace ( array ( '\\', "\0" ), array ( '\\\\', "\\\0" ), $str );
                        }
                        return  "'" . str_replace ( "'", $replaceQuote, $str ) . "'";
                    }
    
                    // undo magic quotes for "
                    $str = str_replace ( '\\"','"', $str );
    
                    if ( $replaceQuote == "\\'" ) {// ' already quoted, no need to change anything
                        return "'$str'";
                    }
                    else {// change \' to '' for mssql
                        $str = str_replace ( '\\\\','\\', $str );
                        return "'" . str_replace ( "\\'", $replaceQuote, $str ) . "'";
                    }
                    break;
                default     :   $str = ($str === NULL) ? 'NULL' : $str;
                            return $str;
                    break;
            }
    }    
}
?>
