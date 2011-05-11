<?php
//FILE SUGARCRM flav=int ONLY


require_once('include/ytree/Tree.php');
require_once('include/ytree/Node.php');
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
/*********************************************************************************
 * $Id: AddUserToTeam.php 13782 2006-06-06 17:58:55Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
/**
 * TeamHierarchy represents a team hiearchy and is used for managing and storing the team hierarchy within SugarCRM.
 * It allows for multiple inheritance through the ability to relate multiple teams to different parents.  The idea behind
 * this implementation is to push all of the heavy lifting onto the management phase and try to alleviate as much of the
 * work as possible from the SELECT queries while also implemeting the desired requirements.
 *
 * This implementation is modeled after the Nested Set Model, but with a twist.  In that model typically the lft and rgt
 * would be stored on the teams table, but since we have to support multiple hierarchies, it had to be extracted out
 * to another table: team_hierarchy
 *
 * Example Usage:
 *
 * $focus = new TeamHierarchy();
 * $focus->team_id = 'The team id this record represents';
 * $focus->parent_id = 'The parent of this record';
 * $focus->save();
 *
 * This will save an entry in the team_hierarchy table with default values for lft and rgt of 0.
 *
 * Once we have saved all of the relevant team_hierarchy records. Call:
 *
 * $focus->rebuildTree();
 *
 * This will start at the root where parent_id = NULL and then recurse through the nodes based on the parent_ids and
 * set the appropriate lft and rgt values.
 *
 */
class TeamHierarchy extends SugarBean{
    /*
    * char(36) GUID
    */
    var $id;
    /*
    * the left integer for this team in the hierarchy
    */
    var $lft = 0;
    /*
    * the right integer for this team in the hierarchy
    */
    var $rgt = 0;
    /*
    * The char(36) parent id of this team's parent in the hierarchy
    */
    var $parent_id = null;
    /*
    * The char(36) team id guid that this entry is related to
    */
    var $team_id;
    /*
    * The last datetime this record was modified
    */
    var $date_modified;
    /*
    * Whether this record has been soft deleted or not.
    */
    var $deleted;

    var $table_name = "team_hierarchies";
    var $object_name = "TeamHierarchy";
    var $module_dir = 'Teams';
    var $disable_custom_fields = true;

    /**
    * Default constructor
    *
    */
    public function __construct(){
        parent::SugarBean();
        $this->disable_row_level_security =true;
    }

    /**
    * Rebuild the entire tree starting at the root node.
    *
    */
    public function rebuildTree(){
        //select the root
        $result = $GLOBALS['db']->query("SELECT id FROM $this->table_name WHERE (parent_id is NULL OR parent_id = '')");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if(!empty($row['id'])){
            $this->rebuildSubTree($row['id'], 0);
        }
    }

    /**
    * Rebuild the tree from the node given
    *
    * @param id $parent_id
    * @param int $left
    * @return void
    */
    public function rebuildSubTree($parent_id, $left){
    // the right value of this node is the left value + 1
    $right = $left+1;

    // get all children of this node
    $result = $GLOBALS['db']->query("SELECT id FROM $this->table_name WHERE parent_id='$parent_id'");
    while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
        // recursive execution of this function for each
        // child of this node
        // $right is the current right value, which is
        // incremented by the rebuild_tree function
            $right = $this->rebuildSubTree($row['id'], $right);
    }

    // we've got the left value, and now that we've processed
    // the children of this node we also know the right value
    $query = 'UPDATE '.$this->table_name.' SET lft='.$left.', rgt='.$right.' WHERE id="'.$parent_id.'";';

    $GLOBALS['db']->query($query);

    // return the right value of this node + 1
    return $right+1;
    }

    /**
    * Given a left and right, or if not provided use the bean left, and right, return all of the child nodes.
    *
    * @param int $left
    * @param int $right
    */
    public function getChildren($left = null, $right = null){
        if(is_null($left)){
            $left = $this->left;
        }
        if(is_null($right)){
            $right = $this->right;
        }

        $where = "lft BETWEEN $left AND $right";
        return $this->get_list('lft', $where);
    }

    /**
    * Return the path to a given node
    *
    * @param int $left
    * @param int $right
    * @return path to the given node
    */
    public function getPath($left = null, $right = null){
        if(is_null($left)){
            $left = $this->left;
        }
        if(is_null($right)){
            $right = $this->right;
        }

        $where = "lft < $left AND rgt > $right";
        return $this->get_list('lft', $where);
    }

    /**
    * Return the number of children in a particular branch
    *
    * @param int $left
    * @param int $right
    * @return int the number of children that a branch contains
    */
    public function getNumChildren($left = null, $right = null){
        if(is_null($left)){
            $left = $this->left;
        }
        if(is_null($right)){
            $right = $this->right;
        }
        $num_children = (($right - $left - 1) / 2);
        return $num_children;
    }

    /**
    * Output a javascript representation of the tree.
    *
    */
    public function displayTree(){
        $result = $GLOBALS['db']->query("SELECT $this->table_name.id, teams.name, lft, rgt FROM $this->table_name INNER JOIN teams on teams.id = $this->table_name.team_id WHERE (parent_id is NULL OR parent_id = '')");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $root = $row['id'];
        $tree=new Tree('tree_widget');
        $node = new Node($row['id'], $row['name']);
        $tree->add_node($node);
        $teamH = new TeamHierarchy();
        $teamH->populateFromRow($row);
        $this->buildTree($node, $teamH);
        echo $tree->generate_header();
        echo($tree->generate_nodes_array());
    }

    /**
    * Given a parent tree node, and a team_hierarcy bean, build the branch of the tree.
    *
    * @param Node $parentNode
    * @param TeamHierarchy $focus
    */
    public function buildTree($parentNode, $focus){
        //find all this node's children
        $query = 'SELECT '.$this->table_name.'.*, teams.name FROM '.$this->table_name.' INNER JOIN teams on teams.id = '.$this->table_name.'.team_id '.
                            'WHERE parent_id = \''.$focus->id.'\' ORDER BY lft ASC;';

        // now, retrieve all descendants of the $root node
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $childNode = new Node($row['id'], $row['name']);
            $teamH = new TeamHierarchy();
            $teamH->populateFromRow($row);
            $parentNode->add_node($childNode);
            $this->buildTree($childNode, $teamH);
        }
    }

    /**
    * Given a user_id and a team_hierarchy_id, then go through the hierarchy and add the user approprately
    * based on a trickle down approach.
    *
    * Also have to go back and ensure the reports_to info is updated appropriately
    *
    */
    public function addUserToTeam($user_id, $team_hierarchy_id){
        //add the user explicitly to the team passed in
        //and implicity to hierarchy.
        $teamH = new TeamHierarchy();
        $teamH->retrieve($team_hierarchy_id);
        $membership = new TeamMembership();
        $result = $membership->retrieve_by_user_and_team($user_id, $teamH->team_id);
        if(empty($result)){
            $membership->user_id = $user_id;
            $membership->explicit_assign = true;
            $membership->team_id = $teamH->team_id;
            $membership->save();
        }
        $this->addUserToChildTeams($user_id, $team_hierarchy_id);
    }

    public function addUserToChildTeams($user_id, $team_hierarchy_id){
        $query = 'SELECT '.$this->table_name.'.* FROM '.$this->table_name.' WHERE parent_id = \''.$team_hierarchy_id.'\'';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $membership = new TeamMembership();
            $result = $membership->retrieve_by_user_and_team($user_id, $row['team_id']);
            if(empty($result)){
                $membership->user_id = $user_id;
                $membership->implicit_assign = true;
                $membership->team_id = $row['team_id'];
                $membership->save();
            }
            $this->addUserToChildTeams($user_id, $row['id']);
        }
    }
}
?>