<?php
/**
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004 - 2009 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 */
require_once('include/Dashlets/Dashlet.php');
require_once('include/Dashlets/implementations/Paginator/Paginator.php');

class SugarFeeds extends Dashlet {

    private $db;
    private $ss;
    private $feeds;
	private $params = array('fields'=>array());
    private $paginator;
		
	function SugarFeeds($id, $def)
	{
		$this->db = DBManagerFactory::getInstance();
        $this->title = "Feeds";
        $this->ss = new Sugar_Smarty();
        $this->paginator = new GenericPaginator();
		parent::Dashlet($id);
	}

	 
	function getFieldValue($field){
		$val = '';
		if(is_array($field)){
			foreach($field as $f){
				$val .= $this->getFieldValue($f);	
			}	
		}else{
			if(!empty($this->focusBean->field_defs[$field])){
				$val .= !empty($this->focusBean->$field)?$this->focusBean->$field:'' ;
			}else{
				$val .= $field;	
			}	
		}	
		return $val;
	}
	function process()
	{
	    $this->feeds = $this->getFocusBeanFeeds();
        $this->paginator->setTotalCount(count($this->feeds));
	}
	
	

    function getHeader($text='')
	{
	    $this->setPaginator();
	    $this->ss->assign('paginator', $this->paginator->displayPaginator() );
	    $header = $this->ss->fetch('include/DashletContainer/Dashlets/SugarFeeds/tpls/header.tpl');
	    return parent::getHeader($text) . $header;
	}

  	function setPaginator()
	{
	    $this->paginator->setItemsPerPage(5);
	}

	function getFocusBeanFeeds() {
		$query = "select * from SugarFeed where related_id='".$this->focusBean->id."'";
		$results = $this->db->query($query);
        $resultArray = array();
        $feedIds = array();
        while( $row = $this->db->fetchByAssoc($results))
        {
            $id = $row['id'];
            $resultArray[$id] = $row;
            $feedIds[] = "'".$row['id']."'";
        }

   		$query = "select * from SugarFeed where related_id in (";
        $query .= implode(",", $feedIds);
   		$query .= ")";
		$results = $this->db->query($query);
        while( $row = $this->db->fetchByAssoc($results))
        {
            $parentFeedId = $row['related_id'];

            $resultArray[$parentFeedId]['replies'] = $row;

        }
        return $resultArray;
    }
	
	function display()
    {
        //<b>{this.CREATED_BY}</b> {SugarFeed.CREATED_CONTACT} [Contacts:6aa923f2-7cfe-d720-168b-4b9990c47ebb:SnipMe]
        //$feeds = $this->getFocusBeanFeeds();
        /*
        $data = '<div><table>';
        foreach ($this->feeds as $key=>$val) {
            $data .= "<tr><td>".$val['name'];
            $data .="</td></tr>";

            $GLOBALS['current_dc_sugarfeed'] = $this->focusBean->created_by_name;
    		
            $data = preg_replace_callback('/\{([^\}]+)\.([^\}]+)\}/', create_function(
                '$matches',
                'if($matches[1] == "this"){$var = $matches[2]; return $GLOBALS[\'current_dc_sugarfeed\'];}else{return translate($matches[2], $matches[1]);}'
            ),$data);
            $data = preg_replace('/\[(\w+)\:([\w\-\d]*)\:([^\]]*)\]/', '<a href="index.php?module=$1&action=DetailView&record=$2"><img src="themes/default/images/$1.gif" border=0>$3</a>', $data);

            if (!empty($val['replies'])) {
                $data .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$val['replies']['name'];
                $data .="</td></tr>";
            }
        }
        $data .= '</table></div>';

        return parent::display() . $data;
*/
		$feedResuls = array();   
		foreach ($this->feeds as $key=>$val) {
            $data = $val['name'];

            $GLOBALS['current_dc_sugarfeed'] = $this->focusBean->created_by_name;
    		
            $data = preg_replace_callback('/\{([^\}]+)\.([^\}]+)\}/', create_function(
                '$matches',
                'if($matches[1] == "this"){$var = $matches[2]; return $GLOBALS[\'current_dc_sugarfeed\'];}else{return translate($matches[2], $matches[1]);}'
            ),$data);
            $data = preg_replace('/\[(\w+)\:([\w\-\d]*)\:([^\]]*)\]/', '<a href="index.php?module=$1&action=DetailView&record=$2"><img src="themes/default/images/$1.gif" border=0>$3</a>', $data);

            if (!empty($val['replies'])) {
                $data .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$val['replies']['name'];
            }
            $feedResults[] = $data;
        }	
        	     
	    $this->ss->assign('data', $feedResults);
        return  parent::display() . $this->ss->fetch('include/DashletContainer/Dashlets/SugarFeeds/tpls/content.tpl');

	}

}
?>