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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

include_once('include/database/MssqlManager.php');

class FreeTDSManager extends MssqlManager
{

	public $isFreeTDS = true;

	/**
     * @see DBManager::query()
     */
    public function query(
        $sql,
        $dieOnError = false,
        $msg = '',
        $suppress = false
        )
    {
        $sql = $this->appendN($sql);
        parent::countQuery($sql);
        $GLOBALS['log']->info('Query:' . $sql);
        $this->checkConnection();
        $this->query_time = microtime(true);

        if ($suppress) {
            //BEGIN SUGARCRM flav=ent ONLY
            //suppress flag is when you are using CSQL and make a bad query.
            //We don't want any php errors to appear
                $orig_level = error_reporting();
                error_reporting(0);
                $result = mssql_query($sql,$this->database);
                error_reporting($orig_level);
            //END SUGARCRM flav=ent ONLY
        }
        else {
            $result = @mssql_query($sql,$this->database);
        }

        if (!$result) {
            //BEGIN SUGARCRM flav=int ONLY
            _pp($sql);
            display_stack_trace();
            //END SUGARCRM flav=int ONLY

            // awu Bug 10657: ignoring mssql error message 'Changed database context to' - an intermittent
            // 				  and difficult to reproduce error. The message is only a warning, and does
            //				  not affect the functionality of the query
            $sqlmsg = mssql_get_last_message();
            $sqlpos = strpos($sqlmsg, 'Changed database context to');

            if($dieOnError)
                if ($sqlpos !== false)
                    // if sqlmsg has 'Changed database context to', just log it
                    $GLOBALS['log']->debug(mssql_get_last_message() . ": " . $sql );
                else {
                    $GLOBALS['log']->fatal('SQL Error : ' . mssql_get_last_message());
                    sugar_die($GLOBALS['app_strings']['ERR_DB_FAIL']);
                }
            else
                echo 'SQL Error : ' . mssql_get_last_message();

            $GLOBALS['log']->fatal(mssql_get_last_message() . ": " . $sql );
        }
        $this->lastmysqlrow = -1;

        $this->query_time = microtime(true) - $this->query_time;
        $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);

        //BEGIN SUGARCRM flav=pro ONLY
        if ($this->dump_slow_queries($sql)) {
           $this->track_slow_queries($sql);
        }
        //END SUGARCRM flav=pro ONLY

        $this->checkError($msg.' Query Failed:' . $sql, $dieOnError);

        return $result;
    }


    /**
     * This is a utility function to prepend the "N" character in front of SQL values that are
     * surrounded by single quotes.
     *
     * @param  $sql string SQL statement
     * @return string SQL statement with single quote values prepended with "N" character for nvarchar columns
     */
    public function appendN(
        $sql
        )
    {
        // If there are no single quotes, don't bother, will just assume there is no character data
        if (strpos($sql, '\'') === false)
            return $sql;

        $sql = str_replace('\\\'', '<@#@#@ESCAPED_QUOTE@#@#@>', $sql);

        //The only location of three subsequent ' will be at the begning or end of a value.
        $sql = preg_replace('/(?<!\')(\'{3})(?!\')/', "'<@#@#@PAIR@#@#@>", $sql);

        // Flag if there are odd number of single quotes, just continue w/o trying to append N
        if ((substr_count($sql, '\'') & 1)) {
            $GLOBALS['log']->error('SQL statement[' . $sql . '] has odd number of single quotes.');
            return $sql;
        }

        // Remove any remaining '' and do not parse... replace later (hopefully we don't even have any)
        $pairs        = array();
        $regexp       = '/(\'{2})/';
        $pair_matches = array();
        preg_match_all($regexp, $sql, $pair_matches);
        if ($pair_matches) {
            foreach (array_unique($pair_matches[0]) as $key=>$value) {
               $pairs['<@PAIR-'.$key.'@>'] = $value;
            }
            if (!empty($pairs)) {
               $sql = str_replace($pairs, array_keys($pairs), $sql);
            }
        }

        $regexp  = "/(N?\'.+?\')/is";
        $matches = array();
        preg_match_all($regexp, $sql, $matches);
        $replace = array();
        if (!empty($matches)) {
            foreach ($matches[0] as $key=>$value) {
                // We are assuming that all nvarchar columns are no more than 200 characters in length
                // One problem we face is the image column type in reports which cannot accept nvarchar data
                if (!empty($value) && !is_numeric(trim(str_replace(array('\'', ','), '', $value))) && !preg_match('/^\'[\,]\'$/', $value)) {
                      $replace[$value] = 'N' . trim($value, 'N');
                }
            }
        }

        if (!empty($replace))
            $sql = str_replace(array_keys($replace), $replace, $sql);

        if (!empty($pairs))
            $sql = str_replace(array_keys($pairs), $pairs, $sql);

        if(strpos($sql, '<@#@#@PAIR@#@#@>'))
            $sql = str_replace(array('<@#@#@PAIR@#@#@>'), array('\'\''), $sql);

        if(strpos($sql, '<@#@#@ESCAPED_QUOTE@#@#@>'))
            $sql = str_replace(array('<@#@#@ESCAPED_QUOTE@#@#@>'), array('\\\''), $sql);

        return $sql;
    }
}
