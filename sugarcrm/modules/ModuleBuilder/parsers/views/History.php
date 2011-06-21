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
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/constants.php' ;

class History
{

    private $_dirname ; // base directory for the history files
    private $_basename ; // base name for a history file, for example, listviewdef.php
    private $_list ; // the history - a list of history files

    private $_previewFilename ; // the location of a file for preview

    /*
     * Constructor
     * @param string $previewFilename The filename which the caller expects for a preview file
     */
    function __construct ($previewFilename )
    {
        $GLOBALS [ 'log' ]->debug ( get_class ( $this ) . "->__construct( {$previewFilename} )" ) ;
        $this->_previewFilename = $previewFilename ;
        $this->_list = array ( ) ;

        $this->_dirname = dirname ( $this->_previewFilename ) ;
        // create the history directory if it does not already exist
        if (! is_dir ( $this->_dirname ))
        {
            mkdir_recursive ( $this->_dirname ) ;
        }
        $this->_basename = basename ( $this->_previewFilename ) ;
        // Reconstruct the history from the saved files
        foreach ( scandir ( $this->_dirname ) as $filename )
        {
            if ($filename != "." && $filename != "..")
            {
                // history files are of the form {$basename}_{$timestamp}
                if (preg_match ( '/(' . $this->_basename . ')_(.*)/', $filename, $matches ) == 1)
                {
                    $this->_list [ $matches [ 2 ] ] = $matches [ 2 ] ;
                }
            }
        }

        // now sort the files, oldest first
        if (count ( $this->_list ) > 0)
        {
            ksort ( $this->_list ) ;
        }
    }


 /*
     * Get the most recent item in the history
     * @return timestamp of the first item
     */
    function getCount ()
    {
        return count ( $this->_list ) ;
    }

    /*
     * Get the most recent item in the history
     * @return timestamp of the first item
     */
    function getFirst ()
    {
        return end ( $this->_list ) ;
    }

/*
     * Get the oldest item in the history (the default layout)
     * @return timestamp of the last item
     */
    function getLast ()
    {
        return reset ( $this->_list ) ;
    }

    /*
     * Get the next oldest item in the history
     * @return timestamp of the next item
     */
    function getNext ()
    {
        return prev ( $this->_list ) ;
    }

    /*
     * Get the nth item in the history (where the zeroeth record is the most recent)
     * @return timestamp of the nth item
     */
    function getNth ($index)
    {
        $value = end ( $this->_list ) ;
        $i = 0 ;
        while ( $i < $index )
        {
            $value = prev ( $this->_list ) ;
            $i ++ ;
        }
        return $value ;
    }

    /*
     * Add an item to the history
     * @return String   A GMT Unix timestamp for this newly added item
     */
    function append ($path)
    {
        // make sure we don't have a duplicate filename - highly unusual as two people should not be using Studio/MB concurrently, but when testing quite possible to do two appends within one second...
        // because so unlikely in normal use we handle this the naive way by waiting a second so our naming scheme doesn't get overelaborated
        $retries = 0 ;

        $now = TimeDate::getInstance()->getNow();
        //$time = $now->format('c');
        $time = $now->__get('ts');
        while ( (file_exists ( $this->_previewFilename . "_" . $time ) && $retries < 5) )
        {
            $now->modify("+1 second");
            $time = $now->__get('ts');
            $retries ++ ;
        }

        // now we have a unique filename, copy the file into the history
        copy ( $path, $this->_previewFilename . "_" . $time ) ;
        $this->_list [ $time ] = $time ;

        // finally, trim the number of files we're holding in the history to that specified in the configuration
        $max_history = (isset ( $GLOBALS [ 'sugar_config' ] [ 'studio_max_history' ] )) ? $GLOBALS [ 'sugar_config' ] [ 'studio_max_history' ] : 50 ;
        $count = count ( $this->_list ) ;
        // truncate the oldest files, keeping only the most recent $GLOBALS['sugar_config']['studio_max_history'] files (zero=keep them all)
        if (($max_history != 0) && ($count > $max_history))
        {
            // most recent files are at the end of the list, so we strip out the first count-max_history records
            // can't just use array_shift because it renumbers numeric keys (our timestamp keys) to start from zero...
            for ( $i = 0 ; $i < $count - $max_history ; $i ++ )
            {
                $timestamp = reset ( $this->_list ) ;
                unset ( $this->_list [ $timestamp ] ) ;
                if (! unlink ( $this->_dirname . "/" . $this->_basename . "_" . $timestamp ))
                {
                    $GLOBALS [ 'log' ]->warn ( "History.php: unable to remove history file {$this->_basename}_$timestamp from directory {$this->_dirname} - permissions problem?" ) ;
                }
            }
        }

        // finally, remove any history preview file that might be lurking around - as soon as we append a new record it supercedes any old preview, so that must be removed (bug 20130)
        if (file_exists($this->_previewFilename))
        {
            $GLOBALS [ 'log' ]->debug( get_class($this)."->append(): removing old history file at {$this->_previewFilename}");
            unlink ( $this->_previewFilename);
        }

        return $time ;
    }

    /*
     * Restore the historical layout identified by timestamp
     * @param Unix timestamp $timestamp GMT Timestamp of the layout to recover
     * @return GMT Timestamp if successful, null if failure (if the file could not be copied for some reason)
     */
    function restoreByTimestamp ($timestamp)
    {
        $filename = $this->_previewFilename . "_" . $timestamp ;
        $GLOBALS [ 'log' ]->debug ( get_class ( $this ) . ": restoring from $filename to {$this->_previewFilename}" ) ;

        if (file_exists ( $filename ))
        {
            copy ( $filename, $this->_previewFilename ) ;
            return $timestamp ;
        }
        return null ;
    }

    /*
     * Undo the restore - revert back to the layout before the restore
     */
    function undoRestore ()
    {
        if (file_exists ( $this->_previewFilename ))
        {
            unlink ( $this->_previewFilename ) ;
        }
    }

}
