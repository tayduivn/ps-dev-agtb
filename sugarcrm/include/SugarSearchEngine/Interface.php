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




interface SugarSearchEngineInterface{

    /**
    *
    * search()
    *
    * Perform a search against the Full Text Search Engine
    * @abstract
    * @param $query
    * @param int $offset
    * @param int $limit
    * @return void
    */
    public function search($query, $offset = 0, $limit = 20);

    /**
    * flush()
    *
    * Save the data to the Full Text Search engine backend
    * @abstract
    * @return void
    */
    public function flush();

    /**
    * indexBean()
    *
    * Pass in a bean and go through the list of fields to pass to the engine
    * @abstract
    * @param $bean
    * @return void
    */
    public function indexBean($bean, $batched = TRUE);

    /**
    * delete()
    *
    * Delete a bean from the Full Text Search Engine
    * @abstract
    * @param $bean
    * @return void
    */
    public function delete($bean);


    /**
     * Index the entire system.
     *
     * @abstract
     *
     */
    public function performFullSystemIndex();


    /**
     *
     * @abstract
     *
     */
    public function bulkInsert(array $docs);

}


interface SugarSeachEngineResultSet extends Iterator, Countable
{
    /**
     * Get the total hits found by the search criteria.
     *
     * @abstract
     * @return int
     */
    public function getTotalHits();



}

interface SugarSeachEngineResult
{
    /**
     * Get the id of the result
     *
     * @abstract
     * @return String The id of the result, typically a SugarBean id.
     */
    public function getId();

    /**
     * @abstract
     *
     */
    public function getHighlightedHitText();

    /**
     * @abstract
     *
     */
    public function getHighlightedFieldName();

    public function __toString();


}