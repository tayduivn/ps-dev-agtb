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
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $
 *********************************************************************************/



    /**get_fts_list
     *
     * This method takes a where clause and uses the list view framework to return a list form for fts
     * returns html for list form with results from a full text search query
     * @param $where where clause to use, typically retrieved from create_fts_search_list_query method
     * @param $ajaxSort if set to true, then sort urls on header of list will point to ajax call.
     *        if set to false, non ajax navigation will take place and screen will refresh (standard behavior)
     */

    function get_fts_list($qry_arr,$isMultiSelect=false,$ajaxSort=false){
    global $current_language,$current_user;
    global $urlPrefix, $currentModule, $theme;
    $current_module_strings = return_module_language($current_language, 'KBDocuments');
    // focus_list is the means of passing data to a ListView.
    global $focus_list;


    require_once('include/ListView/ListViewSmarty.php');
    $view = new SugarView();
    $view->type = 'list';
    $view->module = 'KBDocuments';
    $metadataFile = $view->getMetaDataFile();
    require_once($metadataFile);
    require_once('modules/KBDocuments/KBListViewData.php');


    // clear the display columns back to default when clear query is called
    if(!empty($_REQUEST['clear_query']) && $_REQUEST['clear_query'] == 'true')
        $current_user->setPreference('ListViewDisplayColumns', array(), 0, $currentModule);

    $savedDisplayColumns = $current_user->getPreference('ListViewDisplayColumns', $currentModule); // get user defined display columns
    $json = getJSONobj();
    $seedDocument = new KBDocument(); // seed bean

    // setup listview smarty
    $lv = new ListViewSmarty();
    $lv->lvd= new KBListViewData();

    $displayColumns = array();
    // check $_REQUEST if new display columns from post
    if(!empty($_REQUEST['displayColumns'])) {
        foreach(explode('|', $_REQUEST['displayColumns']) as $num => $col) {
            if(!empty($listViewDefs['KBDocuments'][$col]))
                $displayColumns[$col] = $listViewDefs['KBDocuments'][$col];
        }
    }
    elseif(!empty($savedDisplayColumns)) { // use user defined display columns from preferences
        $displayColumns = $savedDisplayColumns;
    }
    else { // use columns defined in listviewdefs for default display columns
        foreach($listViewDefs['KBDocuments'] as $col => $params) {
            if(!empty($params['default']) && $params['default'])
                $displayColumns[$col] = $params;
        }
    }
    //disable mass update form
    $params = array('massupdate' => false);

    //process orderBy if set in request
    if(!empty($_REQUEST['orderBy'])) {
        $params['orderBy'] = $_REQUEST['orderBy'];
        $params['overrideOrder'] = true;
        if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
    }

    //if ajax sort is set, then pass in param to display columns array that will change
    //the sort urls to be javascript based within tpl
    if($ajaxSort){
        foreach($displayColumns as $col=>$coldata){
            $coldata['ajaxSort'] = true;
            $displayColumns[$col] = $coldata;
        }
    }

    $lv->displayColumns = $displayColumns;


    //grab the where and custom from clauses from passed in query
    $where = '';
    //check to see if param is a string
    if(is_string($qry_arr)){
        //only the where string is passed in, just populate the where
        $where = $qry_arr;
    }elseif(is_array($qry_arr)){
        //array was passed in, populate the where and custom_from
        if(isset($qry_arr['where'])){$where = $qry_arr['where'];}
        if(isset($qry_arr['custom_from'])){$params['custom_from'] = $qry_arr['custom_from'];}
    }

    if (!isset($where)) $where = "";

    global $listViewDefs;

    //disable some features.
    $lv->multiSelect = $isMultiSelect;
    $lv->lvd->additionalDetailsAjax=false;
    $lv->export = false;
    $lv->show_mass_update_form = false;
    $lv->show_action_dropdown = false;
    $lv->delete = false;
    $lv->select = false;

    $lv->setup($seedDocument, 'modules/KBDocuments/SearchListView.tpl', $where, $params);
    $savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);
    //if this is a sort from browse tab, then set the ajaxsort flag to true
    if($ajaxSort){
        $lv->data['pageData']['urls']['ajaxSort'] = true;
    }

    //begin code that will remove single and double quotes for javascript use
    $temp_Data = array();
    //iterate through each record returned in list
	if(isset($lv->data['data']) && $lv->data['data'] != null){
    foreach ($lv->data['data'] as $arrRec){
        //if document name is set, then process
        if(isset($arrRec['KBDOCUMENT_NAME']) && !empty($arrRec['KBDOCUMENT_NAME'])){
            $GLOBALS['log']->info("name of record getting quotes stripped is: ".$arrRec['KBDOCUMENT_NAME']);
            if(!empty($arrRec['KBDOCUMENT_NAME']))
                $temp_name = $arrRec['KBDOCUMENT_NAME'];
                //replace single and double quotes with empty string
                $temp_name = str_replace('&#039;', '', $temp_name);
                $temp_name = str_replace('\'', '', $temp_name);
                $temp_name = str_replace('&quot;', '', $temp_name);
                $temp_name = str_replace('"', '', $temp_name);
                //set to new element variable that will be used for javascript
                $arrRec['KBDOCUMENT_NAME_js'] = $temp_name;
                $temp_Data[] = $arrRec;
        }
    }
	}
    //set records back into data array
    $lv->data['data'] = $temp_Data;



    //return display string, note that display is taking false as parameter so as to disable
    //massupdate form
    return  $lv->display(false);




    }

	/**get_fts_list
	     *
	     * This method takes a where clause and uses the list
	     * returns html for list form with results from a full text search query
	     * @param $where where clause to use, typically retrieved from create_fts_search_list_query method
	     * @param $ajaxSort if set to true, then sort urls on header of list will point to ajax call.
	     *        if set to false, non ajax navigation will take place and screen will refresh (standard behavior)
	     */
function get_admin_fts_list($where,$isMultiSelect=false){
    global $app_strings, $app_list_strings, $current_language, $sugar_version, $sugar_config, $current_user;
    global $urlPrefix, $currentModule, $theme;
    $current_module_strings = return_module_language($current_language, 'KBDocuments');
    // focus_list is the means of passing data to a ListView.
    global $focus_list;


    require_once('include/ListView/ListViewSmarty.php');
	require_once('modules/KBDocuments/metadata/listviewdefs.php');
    require_once('modules/KBDocuments/KBListViewData.php');


	global $app_strings;
	global $app_list_strings;
	global $current_language;
	$current_module_strings = return_module_language($current_language, 'KBDocuments');

	global $urlPrefix;
	global $currentModule;

	global $theme;
	global $current_user;
	// focus_list is the means of passing data to a ListView.
	global $focus_list;

	// setup quicksearch
	//require_once('include/QuickSearchDefaults.php');
	//$qsd = new QuickSearchDefaults();

	// clear the display columns back to default when clear query is called
	if(!empty($_REQUEST['clear_query']) && $_REQUEST['clear_query'] == 'true')
	    $current_user->setPreference('ListViewDisplayColumns', array(), 0, $currentModule);

	$savedDisplayColumns = $current_user->getPreference('ListViewDisplayColumns', $currentModule); // get user defined display columns

	$json = getJSONobj();

	$seedCase = new KBDocument(); // seed bean
	//$searchForm = new SearchForm('KBDocuments', $seedCase); // new searchform instance

	// setup listview smarty
	$lv = new ListViewSmarty();
    $lv->lvd= new KBListViewData();
    $lv->export = false;
    $lv->select = false;
    $lv->delete = false;

	$_REQUEST['action'] = 'index';
	$displayColumns = array();
	// check $_REQUEST if new display columns from post
	if(!empty($_REQUEST['displayColumns'])) {
	    foreach(explode('|', $_REQUEST['displayColumns']) as $num => $col) {
	        if(!empty($listViewDefs['KBDocuments'][$col]))
	            $displayColumns[$col] = $listViewDefs['KBDocuments'][$col];
	    }
	}
	elseif(!empty($savedDisplayColumns)) { // use user defined display columns from preferences
	    $displayColumns = $savedDisplayColumns;
	}
	else { // use columns defined in listviewdefs for default display columns
	    foreach($listViewDefs['KBDocuments'] as $col => $params) {
	        if(!empty($params['default']) && $params['default'])
	            $displayColumns[$col] = $params;
	    }
	}
	$params = array('massupdate' => true); // setup ListViewSmarty params
	if(!empty($_REQUEST['orderBy'])) { // order by coming from $_REQUEST
	    $params['orderBy'] = $_REQUEST['orderBy'];
	    $params['overrideOrder'] = true;
	    if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
	}

	$lv->displayColumns = $displayColumns;


	// use the stored query if there is one
	if (!isset($where)) $where = "";
	require_once('modules/MySettings/StoreQuery.php');
	$storeQuery = new StoreQuery();
	if(!isset($_REQUEST['query'])){
	    $storeQuery->loadQuery($currentModule);
	    $storeQuery->populateRequest();
	}else{
	    $storeQuery->saveFromGet($currentModule);
	}
	if(isset($_REQUEST['query']))
	{
	    // we have a query
	    // first save columns

	    $current_user->setPreference('ListViewDisplayColumns', $displayColumns, 0, $currentModule);
	    if(!empty($_SERVER['HTTP_REFERER']) && preg_match('/action=EditView/', $_SERVER['HTTP_REFERER'])) { // from EditView cancel
	        $searchForm->populateFromArray($storeQuery->query);
	    }
	    else {
	        $searchForm->populateFromRequest();
	    }

	    $where_clauses = $searchForm->generateSearchWhere(true, "kbdocuments"); // builds the where clause from search field inputs

	    if (count($where_clauses) > 0 )$where = implode(' and ', $where_clauses);
	    $GLOBALS['log']->info("Here is the where clause for the list view: $where");
	}

	$lv->export = false;
    $lv->show_mass_update_form = false;
    $lv->show_action_dropdown = false;
    $lv->delete = false;
    $lv->select = false;
    $lv->setup($seedCase, 'modules/KBDocuments/AdminSearchListView.tpl', $where, $params);
	$lv->show_mass_update_form=false;
	$savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);

	$ret_str =  $lv->display(false);
	$json = getJSONobj();
	return $ret_str;

}


   /**
    * get_faq_list
    */
   function get_faq_list($faq_id, $bean) {

       $list = array();


       $childTags = get_child_ids($faq_id, $bean);

       if(count($childTags) == 0) {
          return $list;
       }

       $queryIds = '';
       foreach(array_keys($childTags) as $node) {
               $queryIds .= ",'" . $node . "'";
       }

       $queryIds = substr($queryIds, 1); //remove leading ','
       $query = "select distinct(k.id)as id, k.created_by as created_by from kbdocuments k
                INNER join kbdocuments_kbtags kt on kt.kbdocument_id = k.id ";
       //BEGIN SUGARCRM flav=ent ONLY
       $query .= " AND k.is_external_article = 1";
       //END SUGARCRM flav=ent ONLY
	$query .= "	AND kt.kbtag_id in ($queryIds)";

       $result = $bean->db->query($query);

       while($row = $bean->db->fetchByAssoc($result)) {
             $record = new KBDocument();
             $record->disable_row_level_security = true;
             $id = $row['id'];
             $record->retrieve($id);
             $query = "SELECT first_name, last_name FROM users WHERE id = '".$row['created_by']."'";
             $results = $bean->db->query($query);
             $row2 = $bean->db->fetchByAssoc($results);
             if (!empty ($row2)) {
                 $record->created_by = $row2['first_name'].' '.$row2['last_name'];
             }

             // This gets really expensive... do it outside loop in one shot query
             $list[$id] = $record;
       } //while

       // With the list of KBDocuments on hand, now add the hierarachy information
       $query = "select kb.id as id, kb.kbdocument_id as doc_id, kb.kbtag_id as tag_id, t.parent_tag_id as parent_id, t.tag_name as tag_name
                from kbdocuments_kbtags kb INNER JOIN kbtags t on kb.kbtag_id = t.id AND kb.kbtag_id in ($queryIds)";
       $result = $bean->db->query($query);
       while($row = $bean->db->fetchByAssoc($result)) {
             $rec = $list[$row['doc_id']];
             $rec->tags[] = $row;
       }
       return $list;
   }


   /**
    * get_child_tags
    *
    */
   function get_child_tags($tag, $bean) {
        $list = array();
        $query = "select id, parent_tag_id, tag_name from kbtags where parent_tag_id = '$tag'";
        $result = $bean->db->query($query);
        if(!$result) {
           return $list;
        }

        // Store data as TagNode entries
        while($row = $bean->db->fetchByAssoc($result)) {
              $list[] = array('id'=>$row['id'], 'parent_id'=>$row['parent_tag_id'], 'name'=>$row['tag_name']);
        } //while
        return $list;
   }


   /**
    * get_tag_docs
    *
    */
   function get_tag_docs($tag, $bean) {
        $list = array();

        $spec_SearchVars = array();
     	$spec_SearchVars['exp_date'] = TimeDate::getInstance()->nowDate();
     	$spec_SearchVars['exp_date_filter'] = "after";
   	   	$date_filter = return_date_filter($bean->db->dbType, 'exp_date', $spec_SearchVars['exp_date_filter'], $spec_SearchVars['exp_date']);
        $date_filter = str_replace("kbdocuments", "k", $date_filter);
        $date_filter = "($date_filter OR k.exp_date IS NULL)";
        $query = "select distinct(k.id) as doc_id, k.kbdocument_name as doc_name from kbdocuments k
                 INNER join kbdocuments_kbtags kt on kt.kbdocument_id = k.id AND kt.deleted = 0";
       //BEGIN SUGARCRM flav=ent ONLY
       $query .= " AND k.is_external_article = 1";
       //END SUGARCRM flav=ent ONLY
       $query .= " AND k.status_id = 'Published' AND k.deleted = 0 AND " . $date_filter . " AND kt.kbtag_id = '$tag'";

        $result = $bean->db->query($query);
        if(!$result) {
           return $list;
        }

        // Store data as TagNode entries
        while($row = $bean->db->fetchByAssoc($result)) {
              $list[] = array('doc_id'=>$row['doc_id'], 'doc_name'=>$row['doc_name']);
        } //while
        return $list;
   }


   /**
    * get_kbdocument_body
    *
    */
   function get_kbdocument_body($kbdoc_id, $bean) {

        $query="select kbdocument_body from kbcontents where id in
               (select kbcontent_id from kbdocument_revisions where kbdocument_id = '$kbdoc_id')";

        $result = $bean->db->query($query);
        if(!$result) {
           return '';
        }

        $row = $bean->db->fetchByAssoc($result);
        return $row['kbdocument_body'];
   }


   /**
    * get_child_ids
    * This method returns an Array of id=>TagNode value pairs of the $root_id
    * @param $root_id The root id value to query for (i.e. the id column in kbtags table)
    * @param $bean A sugarbean instance
    * @return $results Array of child id=>tag_name key/value pairs found where the root node is $root_id
    */
   function get_child_ids($root_id, $bean) {
        $list = array();
        $query = "select id, parent_tag_id, tag_name from kbtags";
        $result = $bean->db->query($query);
        if(!$result) {
           return $list;
        }

        // Store data as TagNode entries
        $data = array();
        while($row = $bean->db->fetchByAssoc($result)) {
              $data[$row['id']] = new TagNode($row['id'], $row['parent_tag_id'], $row['tag_name']);
        } //while

        $skip = array();
        foreach($data as $tagNode) {
           recursive_search($tagNode, $data, $list, $root_id, $tagNode->id, $skip);
        }
        return $list;
   }

   /**
    * recursive_search
    * This is a recursive function to find documents linked to a particular root tag id.
    * @param $node The current TreeNode instance to search
    * @param $data The Array of all TreeNode(s)
    * @param $list The current Array of matching TreeNodes(s)
    * @param $root_id The root id value to search for
    * @param $id The current TreeNode id value being searched
    */
   function recursive_search($node, &$data, &$list, $root_id, $id, &$skip) {

           if(in_array($id, $skip)) {
              return;
           }

           if($node->id == $root_id) {
              $list[$id] = $id;
              return;
           }

           if(empty($node->parent_id) || $node->parent_id == '') {
              $skip[] = $id;
              return;  // Done at this root level w/o match, add to $skip
           }

           if($node->parent_id == $root_id) {
              //Found match
              $list[$id] = $data[$id];
              return;
           }
           //Search the next level
           recursive_search($data[$node->parent_id], $data, $list, $root_id, $id, $skip);
   }

   class TagNode {
       var $id;
       var $parent_id;
       var $name;

       function TagNode($aId, $aParentTagId, $aTagName) {
          $this->id = $aId;
          $this->parent_id = $aParentTagId;
          $this->name = $aTagName;
       }
   }

    /**
     * create_portal_list_query
     * This is the function that handles the searches called by the KB Portal code.
     * @param $bean A SugarBean instance (used to get dbType and perform queries)
     * @param $order_by String SQL for ORDER BY clause
     * @param $where String SQL for additional WHERE clause
     * @param $keywords Array of keyword arguments ([most_recent_articles] and [most_viewed_articles] are special)
     * @param $row_offset Integer value of row offset for LIMIT queries
     * @param $limit Interger value of LIMIT number
     * @param $recentLimit Integer value for the most recent articles limit
     * @return $result The result set of resulting query
     */
	function create_portal_list_query($bean, $order_by, $where, $keywords, $row_offset, $limit, $test = false) {

		//BEGIN SUGARCRM flav=int ONLY
		if($test) {
		   test_kb_portal_queries($bean, $order_by, $where, $keywords, $row_offset, $limit);
		   die();
		}

		//END SUGARCRM flav=int ONLY
		$searchVars = array();


		//BEGIN SUGARCRM flav=ent ONLY
		$searchVars['is_external_article'] = array('operator'=>'=','filter'=>1);
		//END SUGARCRM flav=ent ONLY
	    $searchVars['status_id'] = array('operator'=>'=','filter'=>'Published');
		$spec_SearchVars = array();

	    //Create the common date filter to check for expiration and exp_date IS NULL
		$date_filter = return_date_filter($bean->db->dbType, 'exp_date', 'after', TimeDate::getInstance()->nowDate(), null);
		$date_filter = "($date_filter OR kbdocuments.exp_date IS NULL) ";

		if(!empty($keywords)) {
		   if(isset($keywords['keywords']) && $keywords['keywords'] == '[most_recent_articles]') {

		   	   $sql = create_most_recent_articles_query($bean, $order_by, $where, $keywords, $row_offset, $limit, $date_filter);
			   if(!empty($limit)) {
				   $sql = preg_replace('/[\s]top[\s]+(\d+)/i', '', $sql);
				   if(preg_match("/LIMIT[\s]+[\d]+,[\d]+/", $sql)) {
				  	  $sql = substr($sql, 0, strpos($sql, "LIMIT"));
				   } //if

		   	   	   $result = $bean->db->limitQuery($sql, $row_offset, $limit, true, "Error retrieving KBDocument list: $sql");
                   return $result;
			   } //if

		   } else if(isset($keywords['keywords']) && $keywords['keywords'] == '[most_viewed_articles]') {

			   $sql = create_most_viewed_articles_query($bean, $order_by, $where, $keywords, $row_offset, $limit, $date_filter, $searchVars, $spec_SearchVars);

		   	   if(!empty($limit)) {
                   // There is an error in the MssqlManager->limitQuery function
                   if($bean->db->dbType == 'mssql') {
                   	  $sql = "SELECT TOP $limit *
							  FROM (SELECT ROW_NUMBER() OVER (ORDER BY kbdocuments_views_ratings.views_number DESC, kbdocuments_views_ratings.date_modified DESC) AS row_number, kbdocuments.*, kbdocuments_views_ratings.views_number
							  FROM kbdocuments LEFT JOIN kbdocuments_views_ratings ON kbdocuments.id = kbdocuments_views_ratings.kbdocument_id
							  WHERE kbdocuments.id IN
                              (SELECT kbdocument_id
                              FROM kbdocument_revisions
                              WHERE deleted = 0 AND latest = 1)";
                              //BEGIN SUGARCRM flav=ent ONLY
                              $sql .= " AND kbdocuments.is_external_article = '1'";
                              //END SUGARCRM flav=ent ONLY
                              $sql .= " AND kbdocuments.status_id = 'Published' AND $date_filter AND kbdocuments.id IN
                              (SELECT TOP 10 kbdocument_id FROM kbdocuments_views_ratings WHERE deleted = 0)) AS a
							  WHERE row_number > $row_offset and row_number < " . ($row_offset + $limit + 1);

                   } else {
			   	   	  $result = $bean->db->limitQuery($sql, $row_offset, $limit, true, "Error retrieving KBDocument list: $sql");
	                  return $result;
                   }
		   	   } //if

		   } else {
	    	   foreach($keywords as $key=>$value) {
	                   if($key == 'keywords') {
	    	   	       	  $spec_SearchVars['searchText_include'] = $value;
	    	   	       } else {
	    	   	       	  $searchVars[$key] = $value;
	    	   	       }
	    	   } //foreach
	    	   $sql = create_fts_search_list_query($bean->db->dbType, $spec_SearchVars, $searchVars, true);
	 	   	   $sql = $sql . " AND " . $date_filter;

			   if(!empty($limit)) {
				   $result = $bean->db->limitQuery($sql, $row_offset, $limit, true, "Error retrieving KBDocument list: ");
			       return $result;
			   }

		   } //if-else
		} //if

        if(isset($sql)) {
		   $result = $bean->db->query($sql, true);
	       return $result;
        }
        return null;
	}

    /**
     * create_portal_most_recent_query()
     * This method builds and returns query for most recent documents for portal
     * @param $dbType type of db to build this query for, ie oci8, mysql, mssql
     * @param $n the number of articles to return.  Default is 10
     * @param $where clause to use in where portion of query, if needed.
     */
    function create_portal_most_recent_query($dbtype ='mysql', $n = '10', $where='') {

        $portal_most_recent_query = ' ';
        //create portal most recent query, default is top 10
        if($dbtype == 'mysql'){

            $portal_most_recent_query =  'Select kbdocuments.* from kbdocuments where ';
            $portal_most_recent_query .= ' kbdocuments.deleted = 0';
            //BEGIN SUGARCRM flav=ent ONLY
            $portal_most_recent_query .= '  AND kbdocuments.is_external_article = 1 ';
             //END SUGARCRM flav=ent ONLY
            //add where clause if specified
            if (!empty($where)){
                $portal_most_recent_query .= $where;
            }
            $portal_most_recent_query .= " order by active_date desc LIMIT 0,$n ";


        }elseif($dbtype == 'mssql'){//handle sqlserver
            $portal_most_recent_query =  " Select top $n kbdocuments.* from kbdocuments where ";
            $portal_most_recent_query .= ' kbdocuments.deleted = 0 ';
            //BEGIN SUGARCRM flav=ent ONLY
            $portal_most_recent_query .= '  AND kbdocuments.is_external_article = 1 ';
             //END SUGARCRM flav=ent ONLY
            //add where clause if specified
            if (!empty($where)){
                $portal_most_recent_query .= $where;
            }
            $portal_most_recent_query .= 'order by active_date desc';


        }else{//handle oracle
            $portal_most_recent_query =  ' Select * from (SELECT  * from kbdocuments WHERE ';
            $portal_most_recent_query .= ' kbdocuments.deleted = 0 ';
            //BEGIN SUGARCRM flav=ent ONLY
            $portal_most_recent_query .= '  AND kbdocuments.is_external_article = 1 ';
             //END SUGARCRM flav=ent ONLY

            //add where clause if specified
            if (!empty($where)){
                $portal_most_recent_query .=  $where;
            }
            $portal_most_recent_query .= ' order by active_date desc) WHERE rownum <'.($n+1) ;
        }

        return $portal_most_recent_query;

    }

    /**getQSAuthor()
     *
     * This method sets up array for use with quicksearch framework.  Populates values for kb author
     */
    function getQSAuthor($form = 'EditView') {
        global $app_strings;

        $qsUser = array('form' => $form,
                        'method' => 'get_user_array', // special method
                        'field_list' => array('user_name', 'id'),
                        'populate_list' => array('kbarticle_author_name', 'kbarticle_author_id'),
                        'conditions' => array(array('name'=>'user_name','op'=>'like_custom','end'=>'%','value'=>'')),
                        'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
        return $qsUser;
    }



    /**getQSApprover()
     *
     * This method sets up array for use with quicksearch framework.  Populates values for kb approver
     */
    function getQSApprover($form = 'EditView') {
        global $app_strings;

        $qsUser = array('form' => $form,
                        'method' => 'get_user_array', // special method
                        'field_list' => array('user_name', 'id'),
                        'populate_list' => array('kbdoc_approver_name', 'kbdoc_approver_id'),
                        'required_list' => array('kbdoc_approver_id'),
                        'conditions' => array(array('name'=>'user_name','op'=>'like_custom','end'=>'%','value'=>'')),
                        'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
        return $qsUser;
    }
    function getQSTags($form = 'EditView') {
		global $app_strings;

		$qsTags = array('form' => $form,
		                'method' => 'query',
		                'modules'=> array('KBTags'),
		                'group' => 'or',
		                'field_list' => array('tag_name','id'),
		                'populate_list' => array('tag_name'),
		                'conditions' => array(array('name'=>'tag_name','op'=>'like_custom','end'=>'%','value'=>''),
							                       array('name'=>'tag_name','op'=>'like_custom','begin'=>'(','end'=>'%','value'=>'')),
		                'order' => 'tag_name',
		                'limit' => '30',
		                'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
		return $qsTags;
	}

    /**getQSFileName()
     *
     * This method sets up array for use with quicksearch framework.  Populates values for document revision file name
     */
    function getQSFileName() {
        global $app_strings;

        $qsFileName = array(    'method' => 'query',
                                'modules'=> array('DocumentRevisions'),
                                'group' => 'or',
                                'field_list' => array('filename'),
                                'populate_list' => array('filename'),
                                'conditions' => array(array('name'=>'filename','op'=>'like_custom','end'=>'%','value'=>'')),
                                'limit' => '30', 'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);


        return $qsFileName;
    }


    /**getQSMimeType()
     *
     * This method sets up array for use with quicksearch framework.  Populates values for document revision mime type
     */
    function getQSMimeType() {
        global $app_strings;

        $qsFileName = array(    'method' => 'query',
                                'modules'=> array('DocumentRevisions'),
                                'group' => 'or',
                                'field_list' => array('file_mime_type'),
                                'populate_list' => array('file_mime_type'),
                                'conditions' => array(array('name'=>'file_mime_type','op'=>'like_custom','end'=>'%','value'=>'')),
                                'limit' => '30', 'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);


        return $qsFileName;
    }



    /**getKBStyles
     *
     * This method returns z index values for menu and sub menu dropdowns.  This ensures that dropdown list is always on top
     */
    function getKBStyles(){
     $menuStyle =
                     '<style type="text/css">
                            .menu{
                                z-index:100;
                            }

                            .subDmenu{
                                z-index:100;
                            }
                        </style>';


            return $menuStyle;
    }




    /**create_fts_search_list_query
     *
     * This method takes in parameters in order to construct the where clause used by our list form api's to construct the list form.
     * The parameters that can be used are as follow:
     * @param $dbType The db type this search is for ('oci8', 'mssql', 'mysql')
     * @param $spec_SearchVars Array of parameters used to build special handling into query
     * @param $searchVars Array of parameters used to add bean fields into query
     * @search_str String holding where clause, ready to be passed into list view setup
     */
    function create_fts_search_list_query($dbType='mysql',$spec_SearchVars,$searchVars,$fullQuery=false){


    /**
    $searchVars should be an array of bean variables to search.  Key name should be name of variables and should map directly to the kbdocument table itself.
    example of some acceptable searchVars are:
        $searchVars['kbdocument_name']
        $searchVars['description']
        $searchVars['status_id']

    ** $spec_SearchVars  array is meant to handle special search enhancements.  These enhancements and acceptable params are as follow:

            ** Full Text Search Params.
                $spec_SearchVars['searchText_include']
                $spec_SearchVars['searchText_exclude']

             'searchText_include' are the keywords that will be used to perform the full text search.  Articles returned will contain values from this param.
             'searchText_exclude' are the keywords that will be used to perform the full text search.  Articles returned will contain values from the include param, but exclude values from this param.

            ** Active_Date Filters
                $spec_SearchVars['active_date']         //date used for before/after/on/between_dates filters
                $spec_SearchVars['active_date2']        //used for 'between_dates' filter
                $spec_SearchVars['active_date_filter']  //define filter according to 'kbdocument_date_filter_options' dom object

                These parameters will be used to apply date filters to knowledge document regarding active date field


            ** Exp_Date Date Filters
                $spec_SearchVars['exp_date']            //date used for before/after/on/between_dates filters
                $spec_SearchVars['exp_date2']           //used for 'between_dates' filter
                $spec_SearchVars['exp_date_filter']     //define filter according to 'kbdocument_date_filter_options' dom object

                These parameters will be used to apply date filters to knowledge document regarding exp_date field

            ** Tag Name Filters
                $spec_SearchVars['tag_name']

                This parameter will be used to extract tag names to constrain the search to.  Expects tag name to be formatted as [tagname]

            ** Viewed Frequency Rate Filters
                $spec_SearchVars['frequency']

                This parameter will be used to add on 'viewed frequency' filters, according to the 'kbdocument_viewing_frequency_dom' dom object


            ** Canned Search Filters
                $spec_SearchVars['canned_search']

                This parameter will be used to add on 'canned query' filters, according to the 'kbdocument_canned_search' dom object

            ** Attachment Filters
                $spec_SearchVars['attachments']     //holds dropdown value of desired search filter
                $spec_SearchVars['filename']        //holds filename to search for if desired
                $spec_SearchVars['file_mime_type']  //holds mimetype to search for if desired

                These parameters will be used to apply filters to knowledge document regarding attachments



     *end of  $spec_SearchVars array params
     */

    $qry_arr['where'] ='';
    $qry_arr['custom_from'] ='';


        //create the fts 'include' search string
        $include_quote_token = array();
        $include_srch_str_raw = '*';
        if(isset($spec_SearchVars['searchText_include'])  && !empty($spec_SearchVars['searchText_include'])){
            $spec_SearchVars['searchText'] = $spec_SearchVars['searchText_include'];

            //validate the number of quotes
            if(!validate_quotes($spec_SearchVars['searchText_include'])){
             return '';
            }

            //strip quotes for easier processing
            $include_stripped = stripQuotesKB($spec_SearchVars['searchText_include'],$dbType);
            $include_quote_token = $include_stripped['quote_token'];
            $include_srch_str_raw = $include_stripped['search_string_raw'];
        }

        //search through remaining string and replace commas with space for easier processing
        $include_srch_str_raw = str_replace(',', ' ',$include_srch_str_raw);
        $include_srch_str_raw = trim($include_srch_str_raw);

    //if this is oracle or mssql db
    if($dbType != 'mysql') {

        $i = 0;
        $not_token = array();
        $and_token = array();
        $or_token = array();

        //split search text values into array
        $incl_query_array = explode(" ",$include_srch_str_raw);

        //for each word being searched on, figure out if this word should be in
        //an 'or', 'and', or 'and not' clause
        foreach($incl_query_array as $key=>$val){
            $first_char = ' ';
            $val = ltrim($val);
            if(!empty($val)){
                $first_char = $val{0};
                if($first_char == '+'){
                    //if + is defined, this is an 'and' word
                    $and_token['and_'.$i] =  substr($val,1);
                }elseif($first_char == '-'){
                    //if - is defined, this is an 'and not' word
                    $not_token['not_'.$i] =  substr($val,1);
                }else{
                    //no character defined, this is an 'or' word
                    if(!empty($first_char) || $first_char != '*'){
                        $or_token['or_'.$i] =  $val;
                    }
                }
                $i = $i+1;
            }
        }

        //create method that will take and, not, or, arrays and will construct a
        //string that is almost ready to use as fts filter.  This string is still tokenized
        //and has not had the values in quotes added back in
        $include_search_str =createTokenizedQuery($or_token,$and_token,$not_token,$dbType);
        $include_search_str = trim($include_search_str);

        //time to add values in quotes back in.  Remember, they were taken out because values in
        //quotes are to be treated as one word.
        foreach($include_quote_token as $quote_key => $quote){
                //double quotes have already been added, so remove any stragglers
                $quote = str_replace("\"", "", $quote);
                $include_search_str = str_replace($quote_key, $quote, $include_search_str);
        }
        $include_search_str = trim($include_search_str);

        //now process the 'exclude' portion
        $exclude_search_str = ' ';
        if(isset($spec_SearchVars['searchText_exclude']) && !empty($spec_SearchVars['searchText_exclude']) && !empty($include_search_str) && $include_search_str !='*'){

            //validate the number of quotes
            if(!validate_quotes($spec_SearchVars['searchText_exclude'])){
             return '';
            }

            //since this is from exclude field, all words are to be excluded
            //so strip the + or - signs
            $xclude_str = str_replace('+', ' ',$spec_SearchVars['searchText_exclude']);
            $xclude_str = str_replace('-', ' ',$spec_SearchVars['searchText_exclude']);

            //strip out any words enclosed in quotes, for easy processing
            $exclude_stripped = stripQuotesKB($spec_SearchVars['searchText_exclude'],$dbType);
            $exclude_quote_token = $exclude_stripped['quote_token'];
            $exclude_srch_str_raw = $exclude_stripped['search_string_raw'];

            //search through remaining string and replace commas with space for easy processing.
            $exclude_srch_str_raw = str_replace(',', ' ',$exclude_srch_str_raw);

            //create array of search words to exclude
            $excl_query_array = explode(" ",$exclude_srch_str_raw);
            $exclude_search_str =createTokenizedQuery(null,null,$excl_query_array,$dbType,true);
            $exclude_search_str = trim($exclude_search_str);

            //time to add values in quotes back in.  Remember, they were taken out because values in
            //quotes are to be treated as one word.
            foreach($exclude_quote_token as $quote_key => $quote){
                    //double quotes have already been added, so remove any stragglers
                    $quote = str_replace("\"", "", $quote);
                    $exclude_search_str = str_replace($quote_key, $quote, $exclude_search_str);
            }
        }

        //combine the include and exclude strings
        $srch_str_raw = trim($include_search_str) . ' ' .trim($exclude_search_str);
        $srch_str_raw = str_replace("'", "''", $srch_str_raw);

        //do a final pass thru and replace all '%' wildcards with '*' wildcard and trim leading/trailing spaces
        // dreverri - bug #20215
        // Fulltext search wildcards
        // Oracle - '%' (the '*' character is used to weight search terms)
        // MSSQL - '*'
        // MySQL - '*'
        // if Oracle keep wildcards as '%'
        if($dbType != 'oci8') {
        	$srch_str_raw = str_replace("%", "*", $srch_str_raw);
        } else {
        	$srch_str_raw = str_replace("*", "%", $srch_str_raw);
        }
        $srch_str_raw = trim($srch_str_raw);

        //create portion of query that holds the fts search
        $search_str =",
            (
              SELECT kbdocument_id as id FROM kbdocument_revisions WHERE deleted = 0 and latest = 1 ";

            if($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                //if only the * param is defined, then do not perform full text search
                if(trim($srch_str_raw) !== '*' && trim($srch_str_raw) !== '"*"'){
                    //add fulltext search string for oracle
                    $search_str .= "  and kbcontent_id in (
                     select id from kbcontents where deleted = 0
                     and CONTAINS(kbdocument_body, '$srch_str_raw') > 0
                     )";
                }
                //END SUGARCRM flav=ent ONLY

            }else{
                //if only the * param is defined, then do not perform full text search
                if(trim($srch_str_raw) !== '*' && trim($srch_str_raw) !== '"*"'){
                    //add fulltext search string for mssql
                    $search_str .= "  and kbcontent_id in (
                     select id from kbcontents where deleted = 0
                     and contains(kbdocument_body, '$srch_str_raw')
                     )";
                }
            }
         $search_str .= "
            ) derived_table ";

            //assign string to custom from
           $qry_arr['custom_from']=from_html($search_str);

            //reset search string to begin where clause
            $search_str =' kbdocuments.id = derived_table.id ';

            //assign back original search string prior to processing so it can be displayed back to user
            $srch_str_raw = $spec_SearchVars['searchText'];

            //if exclude string has been defined, then reset values for display
            if(isset($spec_SearchVars['searchText_exclude'])  && !empty($spec_SearchVars['searchText_exclude'])){
                if($dbType == 'oci8') {
                    //BEGIN SUGARCRM flav=ent ONLY
                    $exclude_search_str_display = $spec_SearchVars['searchText_exclude'];
                    $exclude_search_str_array = explode(" ",$exclude_search_str_display);

                    //create raw tokenized string
                    $exclude_search_str_display = ' ';
                    foreach($exclude_search_str_array as $not){
                        if(!empty($not)){
                            $exclude_search_str_display .= ' -' . $not . ' ';
                        }
                    }
                    //END SUGARCRM flav=ent ONLY
                }else{

                    $exclude_search_str_display = $exclude_search_str;
                    $exclude_search_str_display = str_replace("AND NOT ", "-", $exclude_search_str_display);
                }

                //final search string for display
                $srch_str_raw .= ' '. $exclude_search_str_display ;
            }


    }else if($dbType == 'mysql') {

                //add words in quotes back in
                foreach($include_quote_token as $quote_key => $quote){
                    //remove quotes
                    $quote = str_replace("'", "\"", $quote );
                    $include_srch_str_raw = str_replace($quote_key, $quote, $include_srch_str_raw);
                }

                //now process the 'exclude' portion
                $exclude_search_str = ' ';
                if(isset($spec_SearchVars['searchText_exclude']) && !empty($spec_SearchVars['searchText_exclude'])){

                //validate the number of quotes
                if(!validate_quotes($spec_SearchVars['searchText_exclude'])){
                 return '';
                }

                //replace words in quotes qith tokens
                $exclude_stripped = stripQuotesKB($spec_SearchVars['searchText_exclude'], $dbType);
                $exclude_quote_token = $exclude_stripped['quote_token'];
                $exclude_srch_str_raw = $exclude_stripped['search_string_raw'];

                //search through tokenized string and replace commas with space.
                str_replace(',', ' ',$exclude_srch_str_raw);

                //create array of words to exclude
                $excl_query_array = explode(" ",$exclude_srch_str_raw);

                //create string that is properly formatted to exclude words in fts filter
                foreach($excl_query_array as $not){
                    if(!empty($not)) $exclude_search_str .= '  -'.$not;
                }

                //add words in quotes back to query.
                foreach($exclude_quote_token as $quote_key => $quote){
                    $exclude_search_str = str_replace($quote_key, $quote, $exclude_search_str);
                }
            }

            //combine include and exclude strings
            $srch_str_raw = $include_srch_str_raw . ' ' . $exclude_search_str;
            $srch_str_raw = str_replace("'", "''", $srch_str_raw);

            //do a final pass thru and replace all '%' wildcards with '*' wildcard and trim leading/trailing spaces
            // dreverri - bug #20215 - swap '*' and '%' in str_replace
            $srch_str_raw = str_replace("%", "*", $srch_str_raw);
            $srch_str_raw = trim($srch_str_raw);

            //create portion of query that holds the fts search
            $search_str =",
            (
              SELECT kbdocument_id as id FROM kbdocument_revisions WHERE deleted = 0 and latest = 1
              ";
              //if only default '*' character exists, do not create fts filter
            if(trim($srch_str_raw) !== '*' && trim($srch_str_raw) !== '"*"'){
                //crate mysql formatted fts filter
                $search_str .= "  and kbcontent_id in (
                                 select id from kbcontents where deleted = 0
                                 and match(kbdocument_body) against('$srch_str_raw'  IN BOOLEAN MODE)
                                 )";
            }

            //end the query string
             $search_str .= "
                ) as derived_table ";

                //assign string to custom from
               $qry_arr['custom_from']=from_html($search_str);

            //reset search string to begin where clause
            $search_str =' kbdocuments.id = derived_table.id ';


        }

        $tag_display =' ';
        $tag_name_string = '';
        $is_first_tag = true;
        //process tags if specified
        if(isset($spec_SearchVars['tag_name'] )  && !empty($spec_SearchVars['tag_name'] )){
            //if tag name exists, and so does id, use id
            if(isset($spec_SearchVars['tag_id'] )  && !empty($spec_SearchVars['tag_id'] )){
                $tag_id_arr = explode(' ', $spec_SearchVars['tag_id']);

                //process each id specified
                foreach($tag_id_arr as $id){
                    $id = trim($id);
                    if(!empty($id)){
                        if($is_first_tag){
                            $tag_name_string .= "'$id'";
                            $is_first_tag = false;
                        }else{
                            $tag_name_string .= ", '$id'";
                        }
                    }
                  }

              //create filter for tags
              $search_str .= "
                  and kbdocuments.id in (
                        select kbdocument_id from kbdocuments_kbtags where kbtag_id in
                        (
                            $tag_name_string
                        )
                    )";


                }else{
                    //if only tag names are specified and not tag ids, then explode
                    //string on open bracket (properly formatted tag names are enclosed in '[]'
                    $tag_name_arr = explode('[', $spec_SearchVars['tag_name']);

                    //extract string from each formatted tag name
                    foreach($tag_name_arr as $name){
                        $name = trim($name);
                        if(!empty($name)){
                            if($is_first_tag){
                                $tag_name_string .= "'$name'";
                                $is_first_tag = false;
                            }else{
                                $tag_name_string .= ", '$name'";
                            }
                          }
                    }

                      //remove remaining brackets from string for querying
                      $tag_name_string = str_replace('[','',$tag_name_string);
                      $tag_name_string = str_replace(']','',$tag_name_string);

                  //create tag filter, based on names
                  $search_str .= "
                      and kbdocuments.id in (
                            select kbdocument_id from kbdocuments_kbtags where kbtag_id in
                            (
                                select id from kbtags where tag_name in($tag_name_string)
                            )
                        )";
                  }
                }



        //now add the rest of fields to query on, based on search vars array
        $search_str .= "";
        if(isset($searchVars)){
            foreach($searchVars as $key=>$val){
                $op = ' like ';
                $constraint = $val;
                //  check to see if array is being passed in.
                if(is_array($val)){
                    //if array is being passed in, then retrieve operator to use
                    //otherwise, operator will default to 'like'
                    if(isset($val['operator']) && !empty($val['operator'])){
                        $op = ' '.$val['operator']. ' ';
                        $constraint = $val['filter'];
                        //set searchstring with passed in operator
                        $search_str .= " and kbdocuments.$key $op '".$constraint."' ";
                    }else{
                        //set search string with like statement if operator is empty
                        $search_str .= " and kbdocuments.$key $op '".$constraint."%' ";
                    }
                }else{
                        //set search string with like statement if no operator specified
                        $search_str .= " and kbdocuments.$key $op '".$constraint."%' ";
                }
            } //foreach
        } //if

        //add the date range filters
        if(isset($spec_SearchVars['active_date_filter']) && !empty($spec_SearchVars['active_date_filter'])){
            $ac =return_date_filter($dbType, 'active_date', $spec_SearchVars['active_date_filter'], $spec_SearchVars['active_date'], $spec_SearchVars['active_date2']);
            if(!empty($ac)){
                    $search_str .= " and $ac";
            }
        }

        if(isset($spec_SearchVars['exp_date_filter']) && !empty($spec_SearchVars['exp_date_filter'])){
            $xd = return_date_filter($dbType, 'exp_date', $spec_SearchVars['exp_date_filter'], $spec_SearchVars['exp_date'], $spec_SearchVars['exp_date2']);
            if(!empty($xd)){
                $search_str .= ' and ' . $xd;
                    }
                }

        //add the Frequency filter
        if(isset($spec_SearchVars['frequency'])  && !empty($spec_SearchVars['frequency'])){
            $frequencyFilter = return_view_frequency_filter($spec_SearchVars['frequency']);
            if(!empty($frequencyFilter)){ $search_str .= $frequencyFilter;}
        }

        //add attachment Search
        if(isset($spec_SearchVars['attachments'])  && !empty($spec_SearchVars['attachments'])){
            $attachmentFilter = return_attachment_filter($dbType, $spec_SearchVars);
            if(!empty($attachmentFilter)){ $search_str .= $attachmentFilter;}
        }

        //finally, add the canned query constraints
        if(isset($spec_SearchVars['canned_search'])  && !empty($spec_SearchVars['canned_search'])){
            $return_can = return_canned_query($dbType,$spec_SearchVars['canned_search']);
            if(!empty($return_can)){ $search_str .= $return_can;}
        }

        //assign where string to query
        $qry_arr['where'] = from_html($search_str);

        //if full query is expected, then prepend with select statement, Default is to pass back query
        //ready for use as a where clause in ListView Object Setup method
        if($fullQuery){
            $search_str_full  = 'Select kbdocuments.*, kbdocuments_views_ratings.views_number';
            $search_str_full .= ' FROM kbdocuments left join kbdocuments_views_ratings ON kbdocuments.id = kbdocuments_views_ratings.kbdocument_id  ';
            $search_str_full .= $qry_arr['custom_from'] ;
            $search_str_full .= ' where '.$qry_arr['where'] ;
            return $search_str_full;
        }
       return $qry_arr;
}




    /**return_date_filter
     *
     * This method places all words inside quotes into an array and replaces their place
     * in string with a token.  Both array of words and tokenized string are returned.
     *
     * @param $field name of field to process, active_date or exp_date
     * @param $filter name of filter type used to return filter
     * @param $filter_date if needed, date to be used in filter
     * @param $filter_date2 if needed, 2nd date to be used in 'between_dates' filter
     * @param $dbType dbType of install, for example 'mssql', 'mysql', or 'oci8'
     */
function return_date_filter($dbType, $field, $filter, $filter_date='', $filter_date2=''){
    global $timedate;

    //if set, change the dates to be from user display format to db ready format
    if(!empty($filter_date)){
     $filter_date = $timedate->to_db_date($filter_date,false);
    }

    if(!empty($filter_date2)){
     $filter_date2 = $timedate->to_db_date($filter_date2,false);
    }

    if (!empty($filter)){
        $field ='kbdocuments.'.$field;
        if ($filter == 'on'){
            if(!empty($filter_date)){
                if ($dbType == 'oci8') {
                    //BEGIN SUGARCRM flav=ent ONLY
                    return $field . "=TO_DATE('".$GLOBALS['db']->quote($filter_date)."','YYYY-MM-DD') ";
                    //END SUGARCRM flav=ent ONLY
                } else {
                    return $field . "='".$GLOBALS['db']->quote($filter_date)."' ";
                }
            }

        }elseif($filter == 'isnull'){
                if ($dbType == 'oci8') {
                    return  "LENGTH($field ) = 0";

                } elseif($dbType == 'mssql'){

                    return  "($field IS NULL or DATALENGTH($field ) = 0)";
                }else{
                    //return mysql check for null
                    return  "$field IS NULL ";
                }

        }elseif($filter == 'before' && !empty($filter_date)){
            if(!empty($filter_date)){
                if ($dbType == 'oci8') {
                    //BEGIN SUGARCRM flav=ent ONLY
                    return  $field . " < TO_DATE('".$GLOBALS['db']->quote($filter_date)."', 'yyyy-mm-dd') ";
                    //END SUGARCRM flav=ent ONLY
                } else {
                    return $field . " < '".$GLOBALS['db']->quote($filter_date)."' ";
                }
            }

        }elseif($filter == 'after' && !empty($filter_date)){
            if(!empty($filter_date)){
                if ($dbType == 'oci8') {
                    //BEGIN SUGARCRM flav=ent ONLY
                    return  $field . " > TO_DATE('".$GLOBALS['db']->quote($filter_date)."', 'yyyy-mm-dd') ";
                    //END SUGARCRM flav=ent ONLY
                } else {
                    return $field . " > '".$GLOBALS['db']->quote($filter_date)."' ";
                }
            }
        }elseif($filter == 'between_dates' && !empty($filter_date) && !empty($filter_date2)){
            if(!empty($filter_date) && !empty($filter_date2)){
                if ($dbType == 'oci8') {
                    //BEGIN SUGARCRM flav=ent ONLY
                    return $field . " >= TO_DATE('".$GLOBALS['db']->quote($filter_date)."','yyyy-mm-dd') AND   " . $field . "<=TO_DATE('".$GLOBALS['db']->quote($filter_date2)."','yyyy-mm-dd')";
                    //END SUGARCRM flav=ent ONLY
                } else {
                    return $field . ">='" . $GLOBALS['db']->quote($filter_date) . "' AND  " . $field . "<='" . $GLOBALS['db']->quote($filter_date2)."'";
                }
            }
        }elseif($filter == 'last_7_days'){
            if ($dbType  == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                return  $field . " BETWEEN (sysdate - interval '7' day) AND sysdate";
                //END SUGARCRM flav=ent ONLY
            } elseif ($dbType  == 'mssql'){
                return "DATEDIFF ( d ,  " . $field . " , GETDATE() ) <= 7 and DATEDIFF ( d ,  " . $field . " , GETDATE() ) >= 0";
            }else{
                return "LEFT(" . $field . ",10) BETWEEN LEFT((current_date - interval '7' day),10) AND LEFT(current_date,10)";
            }
        }elseif($filter == 'next_7_days'){
            if ($dbType  == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                return $field . " BETWEEN sysdate AND (sysdate + interval '7' day)";
                //END SUGARCRM flav=ent ONLY
            } elseif ($dbType == 'mssql'){
                return "DATEDIFF ( d , GETDATE() ,  " . $field . " ) <= 7 and DATEDIFF ( d , GETDATE() ,  " . $field . " ) >= 0";
            } else {
                return "LEFT(" . $field . ",10) BETWEEN LEFT(current_date,10) AND LEFT((current_date + interval '7' day),10)";
            }

        }elseif($filter == 'next_month'){
            if ($dbType  == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                  return "TRUNC(" . $field . ",'MONTH') = TRUNC(add_months(sysdate,+1),'MONTH')";
                //END SUGARCRM flav=ent ONLY
            } elseif ($dbType == 'mssql'){
                    return "LEFT(" . $field . ",4) = LEFT( (DATEADD(mm,1,GETDATE())),4) and DATEPART(yy," . $field . ") = DATEPART(yy, GETDATE()) ";
            } else {
                return "LEFT(" . $field . ",7) = LEFT( (current_date  + interval '1' month),7)";
            }


        }elseif($filter == 'last_month'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                  return "TRUNC(" . $field . ",'MONTH') = TRUNC(add_months(sysdate,-'1'),'MONTH')";
                //END SUGARCRM flav=ent ONLY
            } elseif ($dbType == 'mssql'){
                return "LEFT(" . $field . ",4) = LEFT((DATEADD(mm,-1,GETDATE())),4) and DATEPART(yy," . $field . ") = DATEPART(yy, GETDATE()) ";
            } else {
                return "LEFT(" . $field . ",7) = LEFT( (current_date  - interval '1' month),7)";
            }

        }elseif($filter == 'this_month'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                return "TRUNC(" . $field . ",'MONTH') = TRUNC((sysdate),'MONTH')";
                //END SUGARCRM flav=ent ONLY
            } elseif ($dbType == 'mssql'){
                    return "LEFT (" . $field . ",4) = LEFT( GETDATE(),4) and DATEPART(yy," . $field . ") = DATEPART(yy, GETDATE()) ";
            } else {
                return "LEFT(" . $field . ",7) = LEFT( current_date,7)";
            }


        }elseif($filter == 'last_30_days'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                return $field . " BETWEEN (sysdate - interval '30' day) AND sysdate";
                //END SUGARCRM flav=ent ONLY
            } elseif ($dbType == 'mssql'){
                return "DATEDIFF ( d ,  " . $field . " , GETDATE() ) <= 30 and DATEDIFF ( d ,  " . $field . " , GETDATE() ) >= 0";
            }else{
                return "LEFT(" . $field . ",10) BETWEEN LEFT((current_date - interval '30' day),10) AND LEFT(current_date,10)";
            }


        }elseif($filter == 'next_30_days'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                return $field  . " BETWEEN (sysdate) AND (sysdate + interval '1' month)";
                //END SUGARCRM flav=ent ONLY
            }elseif ($dbType == 'mssql'){
                return "DATEDIFF ( d , GETDATE() ,  " . $field . " ) <= 30 and DATEDIFF ( d , GETDATE() ,  " . $field . " ) >= 0";
            } else {
                return $field  . " BETWEEN (current_date) AND (current_date + interval '1' month)";
            }

        }elseif($filter == 'this_year'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                return "TRUNC(" . $field . ",'YEAR') = TRUNC( sysdate,'YEAR')";
                //END SUGARCRM flav=ent ONLY
            }elseif ($dbType == 'mssql') {
                return "DATEPART(yy," . $field . ") = DATEPART(yy, GETDATE()) ";
            }else{
                return "LEFT(" . $field . ",4) = EXTRACT(YEAR FROM ( current_date ))";
            }
        }elseif($filter == 'last_year'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                  return "TRUNC(" . $field . ",'YEAR') = TRUNC(add_months(sysdate,-12),'YEAR')";
                //END SUGARCRM flav=ent ONLY
            }elseif ($dbType == 'mssql') {
                return "DATEPART(yy," . $field . ") = DATEPART(yy,( dateadd(yy,-1,GETDATE()))) ";
            } else {
                return "LEFT(" . $field . ",4) = EXTRACT(YEAR FROM ( current_date  - interval '1' year))";
            }


        }elseif($filter == 'next_year'){
            if ($dbType == 'oci8') {
                //BEGIN SUGARCRM flav=ent ONLY
                  return "TRUNC(" . $field . ",'YEAR') = TRUNC(add_months(sysdate,+12),'YEAR')";
                //END SUGARCRM flav=ent ONLY
            }elseif ($dbType == 'mssql') {
                   return "DATEPART(yy," . $field . ") = DATEPART(yy,( dateadd(yy, 1,GETDATE()))) ";
            } else {
                return "LEFT(" . $field . ",4) = EXTRACT(YEAR FROM ( current_date  + interval '1' year))";
            }

        }
    }

}


    //BEGIN SUGARCRM flav=ent ONLY
    /**escape_oracle_key_words
     *
     * This method takes Oracle keywords specified in array that
     * and available to CONTAINS Oracle function and
     * escapes them so as to avoid errors during query execution
     *
     * @param $srch_str_raw string to be processed
     */
    function escape_oracle_key_words($srch_str_raw){
     global $sugar_config;
     $escaped_str = strtolower($srch_str_raw);

         if(!empty($escaped_str)){
            //define arrays of key words and characters to escape
            $ociKeywordArray = array('about', 'accum', 'and', 'bt', 'btg', 'bti', 'btp', 'equiv', 'fuzzy', 'haspath', 'inpath', 'mdata', 'minus', 'near', 'not', 'nt', 'ntg', 'nti', 'ntp', 'or', 'pt', 'rt', 'sqe', 'syn', 'tr', 'trsyn', 'tt', 'within');
            $ociCharacterArray = array('=','?','{' ,'}','\\','(' ,')','[',']',';','~','|','$','!','>','_');

         //override arrays if already defined in sugar config
         if(isset($sugar_config['ociFullTextReservedWords'])  && !empty($sugar_config['ociFullTextReservedWords'])){
            $ociKeywordArray = $sugar_config['ociFullTextReservedWords'];
         }
         if(isset($sugar_config['ociFullTextReservedChars'])  && !empty($sugar_config['ociFullTextReservedChars'])){
            $ociCharacterArray = $sugar_config['ociFullTextReservedChars'];
         }


            $escaped_str_arr = explode(' ',$escaped_str);
            $return_string = ' ';
            //override character and word array
            foreach($escaped_str_arr as $tmp_escaped_str){
                $not_escaped = true;

                //do not process if array element is empty
                if(!empty($tmp_escaped_str)){
                     //for each character in array, replace with empty string
                     foreach($ociCharacterArray as $esc1){
                        if($not_escaped){
                            $found = strpos($tmp_escaped_str, $esc1);
                            if($found !== false){
                               //check to see if we need to preserve the '+' or '-' position
                               $first_char = $tmp_escaped_str{0};
                               if($first_char == '-' || $first_char == '+'){
                                    $tmp_escaped_str = $first_char."{".substr($tmp_escaped_str,1)."}";
                                    $not_escaped = false;
                                    // once string is escaped once, we can break out of loop
                                    break;
                               }else{
                                    $tmp_escaped_str = "{".$tmp_escaped_str."}";
                                    $not_escaped = false;
                                    // once string is escaped once, we can break out of loop
                                    break;
                                }
                            }
                        }
                    }//end foreach
                     //only enter 2nd loop if string has not been escaped
                     if($not_escaped){
                         //for each work in array, replace with same word within curly braces
                         foreach($ociKeywordArray as $esc2){
                            $found = strpos($tmp_escaped_str, $esc2);
                            if($not_escaped){
                                if($found !== false){
                                   $first_char = $tmp_escaped_str{0};
                                   if($first_char == '-' || $first_char == '+'){
                                        //check to see if we need to preserve the '+' or '-' position
                                        $tmp_escaped_str = $first_char ."{".substr($tmp_escaped_str,1)."}";
                                        $not_escaped = false;
                                        // once string is escaped once, we can break out of loop
                                        break;
                                   }else{
                                        $tmp_escaped_str = "{".$tmp_escaped_str."}";
                                        $not_escaped = false;
                                        // once string is escaped once, we can break out of loop
                                        break;
                                    }
                                }
                            }
                        }//end foreach
                    }//end if($not_escaped)
                    $return_string .= " $tmp_escaped_str";
                }//end foreach
             }//end if(!empty($tmp_escaped_str)
         }
     //return escaped string
     return $return_string;
    }
    //END SUGARCRM flav=ent ONLY


    /**stripQuotes
     *
     * This method places all words inside quotes into an array and replaces their place
     * in string with a token.  Both array of words and tokenized string are returned.
     *
     * @param $srch_str_raw string to be processed for quoted words
     * @param $dbType dbType of install, for example 'mssql', 'mysql', or 'oci8'
     */
    function stripQuotesKB($srch_str_raw, $dbType){
            //lets look for paired quotes and tokenize them
            $quote_token = array();
            $first_quote = 0;
            $last_quote = 1;
            $i=0;
            $dub = "\"";
            $esc_dub = "\"";
            $srch_str_raw = from_html($srch_str_raw);
            //replace excaped double quotes with token
            $esc_dub_count = substr_count($srch_str_raw,"\\\"");
            $srch_str_raw = str_replace("\\\"", "##dub##", $srch_str_raw);


            //remove double quotes
            while($last_quote!==false  || $i<30){
                $first_quote = strpos($srch_str_raw,$dub);
                $last_quote = strpos($srch_str_raw,$dub, $first_quote+1);
                if($last_quote!==false){
                    $quote_token['#quote_'.$i.'#'] = substr($srch_str_raw,$first_quote, ($last_quote - $first_quote+1));

                    //if this is oracle, each subsequent word must be prepended with 'and'
                    if($dbType == 'oci8'){
                        //BEGIN SUGARCRM flav=ent ONLY
                        $orclStr = $quote_token['#quote_'.$i.'#'];
                        $orclStr_arr = explode(' ', $orclStr);
                        $newOrclStr = ' ';
                        $orclFirst = true;
                        foreach($orclStr_arr as $word){
                            $word = trim($word);
                            if(!empty($word)){
                                if($orclFirst){
                                    $newOrclStr .= " $word";
                                    $orclFirst = false;
                                }else{
                                    $newOrclStr .= " AND $word";
                                }
                            }
                        }
                        $srch_str_raw = str_replace($orclStr, "(#quote_$i#)", $srch_str_raw);
                        $quote_token['#quote_'.$i.'#'] = $newOrclStr;
                    //END SUGARCRM flav=ent ONLY
                    }else{

                        $srch_str_raw = str_replace($quote_token['#quote_'.$i.'#'], "#quote_$i#", $srch_str_raw);
                      }


                }else{
                    break;
                }
                $i = $i+1;
            }

             //add escaped quotes back in and escape the quote
             if($esc_dub_count && $esc_dub_count>0){
                $quote_temp = array();
                 foreach($quote_token as $k=>$v ){
                    if($dbType == 'mssql'){
                      $quote_temp[$k]  = str_replace( "##dub##", "\"\"", $v);
                    }else{
                        $quote_temp[$k]  = str_replace( "##dub##", "\\\"", $v);
                    }

                 }
                 $quote_token = $quote_temp;
                    if($dbType == 'mssql'){
                        $srch_str_raw = str_replace( "##dub##", "\"\"", $srch_str_raw);
                    }else{
                        $srch_str_raw = str_replace( "##dub##", "\\\"", $srch_str_raw);
                    }

             }


        $return_arr['search_string_raw'] = $srch_str_raw;
        $return_arr['quote_token'] = $quote_token;
        return $return_arr;
    }



    /**createTokenizedQuery
     *
     * This method returns query formatted for use in full text search using the
     * provided arrays.  This method is not needed for mysql, only for mssql and oci8
     * @param $or_token_arr values to be used to construct 'or' statements
     * @param $and_token_arr values to be used to construct 'and' statements
     * @param $not_token_arr values to be used to construct 'and not' statements
     * @param $dbType dbType of install, for example 'mssql', 'mysql', or 'oci8'
     */
    function createTokenizedQuery($or_token_arr,$and_token_arr,$not_token_arr,$dbtype,$exclude_only=false){

            $search_str =" ";
            $isFirst = true;
            if($exclude_only){
                $isFirst = false;
            }
            if($dbtype != 'mysql'){

                //add Or's back in
                if(isset($or_token_arr) && count($or_token_arr)>0){
                    foreach($or_token_arr as $or){
                        if(!empty($or)){
                            if($isFirst){
                                if($dbtype == 'oci8') {
                                    $search_str .= " " . $or;
                                }else{
                                    $search_str .= " \"" . $or ."\"";
                                }
                                $isFirst = false;
                            }else{
                                if($dbtype == 'oci8') {
                                    $search_str .= " OR " . $or;
                                }else{
                                    $search_str .= " OR \"" . $or ."\"";
                                }
                            }
                        }
                    }
                }
                //add Ands
                if(isset($and_token_arr) && count($and_token_arr)>0){
                    foreach($and_token_arr as $and){
                        if(!empty($and)){
                            if($isFirst){
                                if($dbtype == 'oci8') {
                                    $search_str .= " " . $and;
                                }else{
                                    $search_str .= " \"" . $and ."\"";
                                }
                                $isFirst = false;
                            }else{
                                if($dbtype == 'oci8') {
                                    $search_str .= " AND " . $and;
                                }else{
                                    $search_str .= " AND \"" . $and ."\"";
                                }
                            }
                        }
                    }
                }

                //add Nots
                if(isset($not_token_arr) && count($not_token_arr)>0){
                    if($dbtype == 'oci8') {
                    //BEGIN SUGARCRM flav=ent ONLY
                        $search_str .= ' ';
                        $not_strt = '';
                        $not_string = '';
                        $not_end = '';
                        foreach($not_token_arr as $not){
                            if(!empty($not)){
                                if($isFirst){
                                    $not_strt = '% NOT ( ';
                                    if(empty($not_string)){
                                        $not_string .= $not ;
                                        $isFirst = false;
                                        $not_end = ' ) ';
                                    }else{
                                        $not_strt = ' NOT ( ';
                                        $not_string .= " OR $not";
                                        $not_end = ' ) ';
                                    }
                                $isFirst = false;
                                }else{

                                    if(empty($not_strt)){
                                        $not_strt = ' NOT ( ';
                                        $not_string .= "  $not";
                                        $not_end = ' ) ';
                                    }else{
                                     $not_string .= " OR $not";
                                    }
                                }
                            }
                        }

                        if(!$isFirst){
                            $search_str .= $not_strt.$not_string.$not_end;
                        }
                    //END SUGARCRM flav=ent ONLY
                    }else{
                        foreach($not_token_arr as $not){
                            if(!empty($not)){
                                if($isFirst){
                                    $search_str .= " \"%\"  AND NOT \"" . $not ."\"";
                                    $isFirst = false;
                                }else{
                                    $search_str .= " AND NOT \"" . $not ."\"";
                                }
                            }
                        }

                    }

                }
            }

            return $search_str;
    }


    /**return_view_frequency_filter
     *
     * This method creates and returns canned query filters
     *
     * @param $freq_search_opt //option specifying frequency filter to create
     */
    function return_view_frequency_filter($freq_search_opt){
        $filter_query = ' ';
        $dbType = $GLOBALS['db']->dbType;
        $isTop = true;

        //process 'top 5' filter
        if($freq_search_opt=='Top_5'){
            if($dbType == 'mssql'){
                $filter_query = 'Select top 5 kbdocument_id from kbdocuments_views_ratings where deleted = 0 and kbdocument_id is not null and DATALENGTH(kbdocument_id) > 0  order by kbdocuments_views_ratings.views_number desc';
            }elseif($dbType == 'mysql'){
                $filter_query = 'Select kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number desc  LIMIT 0, 5';
            }elseif($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                $filter_query = 'Select kbdocument_id from (SELECT rowid as row_num, kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number desc)  WHERE rownum < 6';
                //END SUGARCRM flav=ent ONLY
            }
        }

        //process 'top 10' filter
        if($freq_search_opt=='Top_10'){
            if($dbType == 'mssql'){
                $filter_query = 'Select top 10 kbdocument_id from kbdocuments_views_ratings where deleted = 0 and kbdocument_id is not null and DATALENGTH(kbdocument_id) > 0  order by kbdocuments_views_ratings.views_number desc, kbdocuments_views_ratings.date_modified  desc';
            }elseif($dbType == 'mysql'){
                $filter_query = 'Select kbdocument_id from kbdocuments_views_ratings where deleted = 0  order by kbdocuments_views_ratings.views_number desc, kbdocuments_views_ratings.date_modified  desc  LIMIT 0, 10';
            }elseif($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                $filter_query = 'Select kbdocument_id from (SELECT rowid as row_num, kbdocument_id from kbdocuments_views_ratings where deleted = 0  order by kbdocuments_views_ratings.views_number desc, kbdocuments_views_ratings.date_modified  desc)  WHERE rownum < 11';
                //END SUGARCRM flav=ent ONLY
            }
        }

        //process 'top 20' filter
        if($freq_search_opt=='Top_20'){
            if($dbType == 'mssql'){
                $filter_query = 'Select top 20 kbdocument_id from kbdocuments_views_ratings where deleted = 0 and kbdocument_id is not null and DATALENGTH(kbdocument_id) > 0  order by kbdocuments_views_ratings.views_number desc, kbdocuments_views_ratings.date_modified  desc';
            }elseif($dbType == 'mysql'){
                $filter_query = 'Select kbdocument_id from kbdocuments_views_ratings where deleted = 0  order by kbdocuments_views_ratings.views_number desc, kbdocuments_views_ratings.date_modified  desc LIMIT 0, 20';
            }elseif($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                $filter_query = 'Select kbdocument_id from (SELECT rowid as row_num, kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number desc, kbdocuments_views_ratings.date_modified  desc)  WHERE rownum < 21';
                //END SUGARCRM flav=ent ONLY
            }
        }

        //process 'bot 5' filter
        if($freq_search_opt=='Bot_5'){
            if($dbType == 'mssql'){
                $filter_query = 'Select top 5 kbdocument_id from kbdocuments_views_ratings where deleted = 0 and kbdocument_id is not null and DATALENGTH(kbdocument_id) > 0  order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc';
            }elseif($dbType == 'mysql'){
                $filter_query = 'Select kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc LIMIT 0,5';
            }elseif($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                $filter_query = 'Select kbdocument_id from (SELECT rowid as row_num, kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc)  WHERE rownum < 6';
                //END SUGARCRM flav=ent ONLY
            }
            $isTop = false;
        }

        //process 'bot 10' filter
        if($freq_search_opt=='Bot_10'){
            if($dbType == 'mssql'){
                $filter_query = 'Select top 10 kbdocument_id from kbdocuments_views_ratings where deleted = 0 and kbdocument_id is not null and DATALENGTH(kbdocument_id) > 0  order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified asc';
            }elseif($dbType == 'mysql'){
                $filter_query = 'Select kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified asc  LIMIT 0, 10';
            }elseif($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                $filter_query = 'Select kbdocument_id from (SELECT rowid as row_num, kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc)  WHERE rownum < 11';
                //END SUGARCRM flav=ent ONLY
            }
            $isTop = false;
        }

        //process 'bot 20' filter
        if($freq_search_opt=='Bot_20'){
            if($dbType == 'mssql'){
                $filter_query = 'Select top 20  kbdocument_id from kbdocuments_views_ratings where deleted = 0 and kbdocument_id is not null and DATALENGTH(kbdocument_id) > 0  order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc';
            }elseif($dbType == 'mysql'){
                $filter_query = 'Select kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc  LIMIT 0, 20';
            }elseif($dbType == 'oci8'){
                //BEGIN SUGARCRM flav=ent ONLY
                $filter_query = 'Select kbdocument_id from (SELECT rowid as row_num, kbdocument_id from kbdocuments_views_ratings where deleted = 0 order by kbdocuments_views_ratings.views_number asc, kbdocuments_views_ratings.date_modified  asc)  WHERE rownum < 21';
                //END SUGARCRM flav=ent ONLY
            }
            $isTop = false;
        }

        if($dbType == 'mysql'){
            //mysql does not support limits in subqueries, so we need to run query
            //and retrieve id's.  This will allow us to construct an id 'not in' subquery.
            $result = $GLOBALS['db']->query($filter_query);
            $isfirst = true;
            $id_list = "";
            while($row = $GLOBALS['db']->fetchByAssoc($result)) {
             	   $id_list .= ",'" . $row['kbdocument_id'] . "'";
            }
            $filter_query = strlen($id_list) > 0 ? substr($id_list, 1) : "";

        }

        //use value to create query
        $ret_query = "";
        if(!empty($filter_query)) {
           $ret_query = " and kbdocuments.id in ( $filter_query )";
        }

        return $ret_query;
    }


    /**return_canned_query
     *
     * This method creates and returns canned query filters
     *
     * @param $dbType dbType of install, for example 'mssql', 'mysql', or 'oci8'
     * @param $canned_search_opt key value of type of canned search to process
     */
    function return_canned_query($dbType, $canned_search_opt){
            $return_can = '';

             if (isset($canned_search_opt)){
                //process 'articles added last 30 days' filter
                if($canned_search_opt == 'added'){
                    if ($dbType == 'oci8') {
                        //BEGIN SUGARCRM flav=ent ONLY
                        $return_can = " and  kbdocuments.date_entered BETWEEN (sysdate - interval '30' day) AND (sysdate + interval '1' day)";
                        //END SUGARCRM flav=ent ONLY
                    } elseif ($dbType == 'mssql'){
                        $return_can = " and  kbdocuments.date_entered BETWEEN (DATEADD(dd,-30,GETDATE())) AND (DATEADD(dd,1,GETDATE()))";
                    }else {
                        $return_can = " and  kbdocuments.date_entered BETWEEN (current_date - interval '30' day) AND (current_date + interval '1' day)";
                    }
                }

                //process 'articles pending my approval' filter
                if($canned_search_opt == 'pending'){
                    global $current_user;
                    $return_can = "and kbdocuments.status_id='In Review' and kbdocuments.kbdoc_approver_id = '". $current_user->id . "'";
                }

                //process 'articles updated last 30 days' filter
                if($canned_search_opt == 'updated'){
                    if ($dbType == 'oci8') {
                        //BEGIN SUGARCRM flav=ent ONLY
                        $return_can = " and kbdocuments.date_modified  BETWEEN (sysdate - interval '30' day) AND (sysdate + interval '1' day)";
                        //END SUGARCRM flav=ent ONLY
                    } elseif ($dbType == 'mssql'){
                        $return_can = " and  kbdocuments.date_modified BETWEEN (DATEADD(dd,-30,GETDATE())) AND (DATEADD(dd,1,GETDATE()))";
                    }else {
                        $return_can = " and  kbdocuments.date_modified BETWEEN (current_date - interval '30' day) AND (current_date + interval '1' day)";
                    }
                }

                //process 'articles under faq's tag' filter
                if($canned_search_opt == 'faqs'){
                  $return_can .= "
                      and kbdocuments.id in (
                            select kbdocument_id from kbdocuments_kbtags where kbtag_id in
                            (
                                select id from kbtags where tag_name ='faqs'
                            )
                        )";
                }
            }

            return $return_can;
    }



    /**return_attachment_filter
     *
     * This method creates and returns filter string for searching on attachments
     * returns full text search query
     * @param $dbType dbType of install, for example 'mssql', 'mysql', or 'oci8'
     * @param $spec_SearchVars Array of inputs needed for attachment filters.
     */
  function return_attachment_filter($dbType, $spec_SearchVars){

            //retrieve param specifying the filter type to return.
            $attachment_search_opt = $spec_SearchVars['attachments'];
            $return_att ='';

            //process if filter exists
             if (isset($attachment_search_opt)){

                //Create if filter type is set to none
                if($attachment_search_opt == 'none'){
                  if ($dbType == 'oci8') {
                        //BEGIN SUGARCRM flav=ent ONLY
                        $return_att = "and kbdocuments.id not in
                                        (
                                           select kbdocument_id from kbdocument_revisions where LENGTH(kbcontent_id) = 0  or kbcontent_id is null
                                        )";

                        //END SUGARCRM flav=ent ONLY
                    }else if($dbType == 'mssql'){
                    $return_att = " and kbdocuments.id not in
                                    (
                                        select kbdocument_id from kbdocument_revisions where kbcontent_id IS NULL or DATALENGTH(kbcontent_id) = 0
                                    )";

                    }else if($dbType == 'mysql'){
                    $return_att = " and kbdocuments.id not in
                                    (
                                        select kbdocument_id from kbdocument_revisions where kbcontent_id IS NULL

                                     )";

                    }


                }
                //Create if filter type is set to some
                if($attachment_search_opt == 'some'){
                    if ($dbType == 'oci8') {
                        //BEGIN SUGARCRM flav=ent ONLY
                        $return_att = "and kbdocuments.id in
                                        (
                                           select kbdocument_id from kbdocument_revisions where LENGTH(kbcontent_id) = 0  or kbcontent_id is null
                                        )";

                        //END SUGARCRM flav=ent ONLY
                    }else if($dbType == 'mssql'){
                    $return_att = " and kbdocuments.id in
                                    (
                                        select kbdocument_id from kbdocument_revisions where kbcontent_id IS NULL or DATALENGTH(kbcontent_id) = 0
                                    )";

                    }else if($dbType == 'mysql'){
                    $return_att = " and kbdocuments.id in
                                    (
                                        select kbdocument_id from kbdocument_revisions where kbcontent_id IS NULL

                                     )";

                    }
                }

                //Create if filter type is set to name
                if($attachment_search_opt == 'name'){
                    if(isset($spec_SearchVars['filename'])  && !empty($spec_SearchVars['filename'])){
                        $return_att =
                                    " AND kbdocuments.id in
                                    (
                                        select kbdocument_id from kbdocument_revisions where document_revision_id  in
                                        (
                                            select id from document_revisions where deleted = 0 and filename  like '" . $spec_SearchVars['filename'] . "%'
                                        )
                                    )
                                    ";
                    }
                }

                //Create if filter type is set to mime type
                if($attachment_search_opt == 'mime'){
                    if(isset($spec_SearchVars['file_mime_type'])  && !empty($spec_SearchVars['file_mime_type'])){
                        $return_att =
                                    " AND kbdocuments.id in
                                    (
                                        select kbdocument_id from kbdocument_revisions where document_revision_id  in
                                        (
                                            select id from document_revisions where deleted = 0 and file_mime_type  like '" . $spec_SearchVars['file_mime_type'] . "%'
                                        )
                                    )
                                    ";
                    }
                }

            }
            return $return_att;
    }


    /**updateKBView
     *
     * This method increments the viewing count for the provided article id
     * @param $kbid id of article to increment
     */

    function updateKBView($kbid){
        $guid = create_guid();
        //retrieve if already exists
        $query = "select * from kbdocuments_views_ratings where kbdocument_id='$kbid'";
        $result = $GLOBALS['db']->query($query);
        $rowq  =  $GLOBALS['db']->fetchByAssoc($result);

        if(!isset($kbid) || empty($kbid)){
         $GLOBALS['log']->fatal("Kbdocument id is null in updateKBView, this is an error.");
        }

        //update if exists else create a new entry
        if($rowq != null){
           $query = "update kbdocuments_views_ratings set views_number = views_number+1 where kbdocument_id='$kbid'";
           $result = $GLOBALS['db']->query($query);
        }
        else{
          $query = "insert into kbdocuments_views_ratings (id,kbdocument_id,views_number) values('$guid','$kbid',1)";
          $result = $GLOBALS['db']->query($query);
        }

        return $result;
    }

    /**
     * create_most_recent_articles_query
     */
    function create_most_recent_articles_query($bean, $order_by, $where, $keywords, $row_offset, $limit, $date_filter) {
		$where = " AND kbdocuments.status_id = 'Published' AND " . $date_filter;
		$sql = create_portal_most_recent_query($bean->db->dbType, $limit, $where);
		return $sql;
    }

    /**
     * create_most_viewed_articles_query
     */
    function create_most_viewed_articles_query($bean, $order_by, $where, $keywords, $row_offset, $limit, $date_filter, $searchVars, $spec_SearchVars) {
   		$spec_SearchVars['frequency'] = 'Top_10';
   		$sql = create_fts_search_list_query($bean->db->dbType, $spec_SearchVars, $searchVars, true);
   		$sql = str_replace("'Published'", "'Published' AND $date_filter ", $sql);
   		return $sql;
    }

    //BEGIN SUGARCRM flav=int ONLY
    /**
     * test_kb_portal_queries
     *
     * This is an internal test method to facilitate testing the KB Portal Queries.
     * To invoke, follow these three steps:
     * 1) Uncomment the line in create_portal_list_query_method that calls this method and set the $test param to true.
     * 2) Fill in the database values accordingly in the $dbs Array for the databases you wish to test.
     * 3) In the Portal code, uncomment the _pp line in include/Portal/Portal.php file for getEntries() method.
     */
    function test_kb_portal_queries($bean, $order_by, $where, $keywords, $row_offset, $limit) {

        //Begin Setup
		$searchVars = array();

        //BEGIN SUGARCRM flav=ent ONLY
		$searchVars['is_external_article'] = array('operator'=>'=','filter'=>1);
        //END SUGARCRM flav=ent ONLY

		$searchVars['status_id'] = array('operator'=>'=','filter'=>'Published');
		$spec_SearchVars = array();

		$date_filter = return_date_filter($bean->db->dbType, 'exp_date', 'after', date($GLOBALS['timedate']->dbDayFormat), null);
		$date_filter = "($date_filter OR kbdocuments.exp_date IS NULL) ";

		$dbs = array(
                     //'mysql' => array('db_host_name'=>'localhost',       'db_host_instance'=>'',            'db_user_name'=>'root',     'db_password'=>'',          'db_name'=>'ent451d'),
                     //'mssql' => array('db_host_name'=>'collin-d620',     'db_host_instance'=>'sqlexpress',  'db_user_name'=>'sa',       'db_password'=>'password',    'db_name'=>'ent451d'),
                     //'oci8'  => array('db_host_name'=>'localhost',       'db_host_instance'=>'',            'db_user_name'=>'ent451d',  'db_password'=>'password',  'db_name'=>'sugarcrm'),
               );
		//End Setup
		global $sugar_config, $dbinstances;

		foreach($dbs as $db_type => $db) {

		echo "<p><h1>Testing database: " . $db_type . "</h1><br><hr><p>";
		//Configure the $sugar_config values
		//This does not work yet so we can only test 1 db at a time
		$sugar_config['dbconfig']['db_type'] = $db_type;
		$sugar_config['dbconfig']['db_host_name'] = $db['db_host_name'];
		$sugar_config['dbconfig']['db_host_instance'] = $db['db_host_instance'];
		$sugar_config['dbconfig']['db_user_name'] = $db['db_user_name'];
		$sugar_config['dbconfig']['db_password'] = $db['db_password'];
	    $sugar_config['dbconfig']['db_name'] = $db['db_name'];

	    $bean = new KBDocument();
	    $bean->disable_row_level_security = true;

	    $tests = array("Running tests for Most Recent Articles" => '[most_recent_articles]',
	                   "Running tests for Most Viewed Articles" => '[most_viewed_articles]',
	                   "Running tests for Keyword Search" => 'Test*');

		    foreach($tests as $name=>$keyword) {
		       echo "<h3>". $name . "........";
		       $keywords['keywords'] = $keyword;
		       $testPaging = $keywords['keywords'] == '[most_recent_articles]' || $keywords['keywords'] == '[most_viewed_articles]';
		       test_kb_portal_results_recursively($bean, $order_by, $where, $keywords, $row_offset, $limit, $testPaging);
		    } //foreach
		} //foreach

    } //test_kb_portal_queries

    /**
     * test_kb_portal_results_recursively
     *
     * This is a recursive function that tests a result set
     */
    function test_kb_portal_results_recursively($bean, $order_by, $where, $keywords, $row_offset, $limit, $testPaging = true, $start = true) {

	    $result = create_portal_list_query($bean, $order_by, $where, $keywords, $row_offset, $limit, false);
        $return_result = test_kb_portal_results($result, $bean);
        if($return_result['error']['description'] == "No Error") {
           if($start) {
           	  echo "Successful!</h3><p>";
           }

           //array_shift($return_result['entry_list']);
           $count = count($return_result['entry_list']);
           if($count == 0) {
           	  return;
           }
           echo "Found <b>$count</b> entries!<br>";


           foreach($return_result['entry_list'] as $entry) {
           	       echo $entry['name_value_list']['kbdocument_name']['value'] . "<br>";
           } //foreach

           // Test paging if the last element has an id value
           if($testPaging && isset($return_result['entry_list'][$count-1]['id'])) {
           	  $new_offset = $row_offset + $limit;
              echo "<br>Testing paging for row_offset=$new_offset, limit=$limit<br>";
              test_kb_portal_results_recursively($bean, $order_by, $where, $keywords, $new_offset, $limit, $testPaging, false);
           } else {
           	  return;
           }

        } else {
           echo "Error found: " . $return_result['error']['description'] . "... aborting<br>";
           die();
        }
    }

    function test_kb_portal_results($result, $bean) {

    	$list = array();
    	if(!isset($result)) {
    	   return "Query failed!";
    	}

		while ($row = $bean->db->fetchByAssoc($result)) {
			   $id = $row['id'];
			   $record = new KBDocument();
			   $record->disable_row_level_security = true;
			   $record->retrieve($id);
			   $record->fill_in_additional_list_fields();
			   $list[] = $record;
		} //while

		require_once('soap/SoapHelperFunctions.php');

		$error = new SoapError();
		$module_name = "KBDocuments";
		$select_fields = array();
		$output_list = array();
		$field_list = array();

		foreach ($list as $value) {

			$output_list[] = get_return_value($value, $module_name);
			$_SESSION['viewable'][$module_name][$value->id] = $value->id;
			if (empty ($field_list)) {
				$field_list = get_field_list($value);
			}
		} //foreach

		$output_list = filter_return_list($output_list, $select_fields, $module_name);
		$field_list = filter_field_list($field_list, $select_fields, $module_name);

	    return array ('result_count' => sizeof($output_list), 'next_offset' => 0, 'field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
    }
    //END SUGARCRM flav=int ONLY


    function validate_quotes($quote_string){
        $esc_quote_string = str_replace("\\\"", "", $quote_string);
        $dubCount = substr_count($esc_quote_string, '"');

        if($dubCount % 2 == 0){
             //throw 'error'
             return true;
        }else{
            return false;
        }

    }