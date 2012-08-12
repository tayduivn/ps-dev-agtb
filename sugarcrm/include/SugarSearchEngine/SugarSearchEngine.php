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
require_once("include/SugarSearchEngine/Interface.php");
require_once('include/SearchForm/SugarSpot.php');

/**
 * This class is an adapter to the existing SugarSpot/UnifiedSearch capabilities and is the default
 * search engine if no other external engines have been configured.
 * @api
 */
class SugarSearchEngine implements SugarSearchEngineInterface{


    public function search($query, $offset = 0, $limit = 20, $options = array() )
    {
        $sugarSpot = new SugarSpot();
        return $sugarSpot->search($query, $offset, $limit, $options);

    }

    /**
     * No-op
     *
     * @param $bean
     */
    public function indexBean($bean, $batched = TRUE) {}

    /**
     * No-op
     *
     * @param $bean
     */
    public function delete(SugarBean $bean) {}


    /**
     * No-op
     */
    public function bulkInsert(array $docs) {}

    /**
     * No-op
     */
    public function createIndexDocument($bean, $searchFields = null) {}

    /**
     * No-op
     */
    public function getServerStatus() {}

    /**
     * No-op
     */
    public function createIndex($recreate = false){}

}