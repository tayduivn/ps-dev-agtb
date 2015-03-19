<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Data;

/**
 * NestedBeanInterface
 *
 * An interface to implement the nested set model for SugarBean.
 *
 * Interface allows to have multiple root nodes in the same table.
 * In this case each of root node can contain different trees to use in
 * different places, modules and etc. The root node is special node and
 * it isn't displayed in UI for end user.
 *
 * Example:
 *
 * // The tree node is stucture with attrigutes:
 *
 * $node = array(
 *     'id' => '...',       // node uid
 *     'name' => '...',     // readable node name
 *     'root' => '...',     // uid of root node
 *     'lft' => '...',      // left index of node in tree
 *     'rgt' => '...',      // right index of node in tree
 *     'lvl' => '...',      // level of node in tree
 *
 *     ... other properties ...
 * );
 *
 * @package Sugarcrm\Sugarcrm\Data
 * @see https://en.wikipedia.org/wiki/Nested_set_model
 */
interface NestedBeanInterface
{
    /**
     * Gets root nodes.
     * @return array List of root nodes.
     */
    public function getRoots();

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function isRoot();

    /**
     * Save current node as new root.
     * @return string Id of new created bean.
     */
    public function saveAsRoot();

    /**
     * Builds from 'flat' tree the hierarchical tree for root node
     *     of current node and returns it as array of arrays.
     *
     * Example:
     *
     * // The format of hierarchical tree is:
     * $tree = array(
     *     array(
     *         'id' => '...',
     *         'name' => '...',
     *         'root' => '...',
     *
     *         ... other properties ...
     *
     *         'children' => array()
     *     ),
     * );
     *
     * // 'children' is array of nodes in format above.
     * 'children' => array(
     *     array(
     *         'id' => '...',
     *         'name' => '...',
     *         'root' => '...',
     *
     *         ... other properties ...
     *
     *         'children' => array()
     *     ),
     * );
     *
     * @param int $depth Max depth to load, null by default (without limitation).
     * @return array[] descendants hierarchy
     *
     * @see modules/Categories/clients/base/api/help/tree_get_tree_help.html
     */
    public function getTree($depth = null);

    /**
     * Gets 'flat' list of children for current node.
     * The returned list is ordered by position node in tree.
     *
     * Example:
     *
     * // The format of 'flat' list is:
     * $children = array(
     *     array(
     *         'id' => '...',
     *         'name' => '...',
     *         'root' => '...',
     *
     *         ... other properties ...
     *     ),
     *     array(
     *         'id' => '...',
     *         'name' => '...',
     *         'root' => '...',
     *
     *         ... other properties ...
     *     ),
     * );
     *
     * @param int $depth Max depth to load for children, by default direct children only.
     * @return array|null List of descendants.
     *
     * @see modules/Categories/clients/base/api/help/tree_get_children_help.html
     */
    public function getСhildren($depth = 1);

    /**
     * Gets next sibling of current node according to position in tree.
     *
     * Example:
     *
     * // The format of returned array is:
     * $nextSibling = array(
     *     'id' => '...',
     *     'name' => '...',
     *     'root' => '...',
     *
     *     ... other properties ...
     * );
     *
     * @return array|null The next sibling node.
     *
     * @see modules/Categories/clients/base/api/help/tree_get_next_help.html
     */
    public function getNextSibling();

    /**
     * Gets previous sibling of current node according to position in tree.
     *
     * Example:
     *
     * // The format of returned array is:
     * $prevSibling = array(
     *     'id' => '...',
     *     'name' => '...',
     *     'root' => '...',
     *
     *     ... other properties ...
     * );
     *
     * @return array The prev sibling node.
     *
     * @see modules/Categories/clients/base/api/help/tree_get_prev_help.html
     */
    public function getPrevSibling();

    /**
     * Gets 'flat' list of all parents of current node.
     * The order of parents depends on position in the tree.
     * By default reverse order is used, that means from direct parent to root node.
     *
     * Example:
     *
     * // The format of 'flat' list is:
     * $parents = array(
     *     array(
     *         'id' => '...',
     *         'name' => '...',
     *         'root' => '...',
     *
     *         ... other properties ...
     *     ),
     *     array(
     *         'id' => '...',
     *         'name' => '...',
     *         'root' => '...',
     *
     *         ... other properties ...
     *     ),
     * );
     *
     * @param int $depth The max depth to looking parents.
     * @param boolean $reverseOrder Use reverse order or not, true by default.
     * @return array the parent nodes.
     */
    public function getParents($depth = null, $reverseOrder = true);

    /**
     * Gets parent of current node.
     *
     * Example:
     *
     * // The format of returned array is:
     * $parent = array(
     *     'id' => '...',
     *     'name' => '...',
     *     'root' => '...',
     *
     *     ... other properties ...
     * );
     *
     * @return array the parent node.
     */
    public function getParent();

    /**
     * Determines if node is descendant of target node.
     * It looks at all levels of parents for current node.
     * Current node is descendant of target if target is parent of current node (direct or not).
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target The parent node to check.
     * @return boolean Whether the node is descendant of target node.
     */
    public function isDescendantOf(NestedBeanInterface $target);

    /**
     * Prepends new node as first child of current node.
     *
     * Example:
     *
     * // Tree state before prepend
     * + - rootNode
     *  |
     *  + - $currentNode
     *    |
     *    + - subNode1
     *
     *
     * // append new node
     * $newNode = BeanFactory::newBean(...);
     * $currentNode = BeanFactory::retrieveBean(...);
     * $newId = $currentNode->prepend($newNode);
     *
     * + - rootNode
     *  |
     *  + - $currentNode
     *    |
     *    + - $newNode
     *    |
     *    + - subNode1
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $node.
     * @return string Id of new created bean;
     *
     * @throw Exception When current bean isn't new bean (existing in Db bean).
     * @throw Exception When current bean is deleted.
     */
    public function prepend(NestedBeanInterface $node);

    /**
     * Append new node as last child of current node.
     *
     * Example:
     *
     * // Tree state before append
     * + - rootNode
     *  |
     *  + - $currentNode
     *    |
     *    + - subNode1
     *
     *
     * // append new node
     * $newNode = BeanFactory::newBean(...);
     * $currentNode = BeanFactory::retrieveBean(...);
     * $newId = $currentNode->append($newNode);
     *
     * + - rootNode
     *  |
     *  + - currentNode
     *    |
     *    + - subNode1
     *    |
     *    + - $newNode
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $node.
     * @return string Id of new created bean;
     *
     * @throw Exception When current bean isn't new bean (existing in Db bean).
     * @throw Exception When current bean is deleted.
     */
    public function append(NestedBeanInterface $node);

    /**
     * Inserts current new node as previous sibling of target node.
     *
     * Example:
     *
     * // Tree state before insertBefore
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - subNode1
     *    |
     *    + - $targetNode
     *
     *
     * // insert new node before target
     * $newNode = BeanFactory::newBean(...);
     * $targetNode = BeanFactory::retrieveBean(...);
     * $newNode->insertBefore($targetNode);
     *
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - subNode1
     *    |
     *    + - $newNode
     *    |
     *    + - $targetNode
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target.
     * @return string Id of new created bean;
     *
     * @throw Exception When current bean isn't new bean (existing in Db bean).
     * @throw Exception When current bean is deleted.
     */
    public function insertBefore(NestedBeanInterface $target);

    /**
     * Inserts current new node as next sibling of target node.
     *
     * Example:
     *
     * // Tree state before insertAfter
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - $targetNode
     *    |
     *    + - subNode1
     *
     *
     * // insert new node after target
     * $newNode = BeanFactory::newBean(...);
     * $targetNode = BeanFactory::retrieveBean(...);
     * $newNode->insertAfter($targetNode);
     *
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - $targetNode
     *    |
     *    + - $newNode
     *    |
     *    + - subNode1
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target.
     * @return string Id of new created bean;
     *
     * @throw Exception When current bean isn't new bean (existing in Db bean).
     * @throw Exception When current bean is deleted.
     */
    public function insertAfter(NestedBeanInterface $target);

    /**
     * Move current existing node as previous sibling of target.
     *
     * Example:
     *
     * // Tree state before moveBefore
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - $targetNode
     *    |
     *    + - subNode1
     *    |
     *    + - $currentNode
     *
     *
     * // move existing node before target
     * $currentNode = BeanFactory::retrieveBean(...);
     * $targetNode = BeanFactory::retrieveBean(...);
     * $currentNode->moveBefore($targetNode);
     *
     * + - parentNode
     *  |
     *  + - parentNode
     *    |
     *    + - $currentNode
     *    |
     *    + - $targetNode
     *    |
     *    + - subNode1
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target The target node move current node before.
     */
    public function moveBefore(NestedBeanInterface $target);

    /**
     * Move current existing node as next sibling of target.
     *
     * Example:
     *
     * // Tree state before moveAfter
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - $currentNode
     *    |
     *    + - subNode2
     *    |
     *    + - $targetNode
     *
     * // move existing node after target
     * $currentNode = BeanFactory::retrieveBean(...);
     * $targetNode = BeanFactory::retrieveBean(...);
     * $currentNode->moveAfter($targetNode);
     *
     * + - rootNode
     *  |
     *  + - parentNode
     *    |
     *    + - subNode2
     *    |
     *    + - $targetNode
     *    |
     *    + - $currentNode
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target The target node move current node after.
     */
    public function moveAfter(NestedBeanInterface $target);

    /**
     * Move current existing node as first child of target.
     *
     * Example:
     *
     * // Tree state before moveAsFirst
     * + - rootNode
     *  |
     *  + - $targetNode
     *    |
     *    + - subNode1
     *    |
     *    + - subNode2
     *    |
     *    + - $currentNode
     *
     * // move existing node as first of target
     * $currentNode = BeanFactory::retrieveBean(...);
     * $targetNode = BeanFactory::retrieveBean(...);
     * $currentNode->moveAsFirst($targetNode);
     *
     * + - rootNode
     *  |
     *  + - $targetNode
     *    |
     *    + - $currentNode
     *    |
     *    + - subNode1
     *    |
     *    + - subNode2
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target the target.
     */
    public function moveAsFirst(NestedBeanInterface $target);

    /**
     * Move current existing node as last child of target.
     *
     * Example:
     *
     * // Tree state before moveAsLast
     * + - rootNode
     *  |
     *  + - $targetNode
     *    |
     *    + - $currentNode
     *    |
     *    + - subNode1
     *    |
     *    + - subNode2
     *
     * // move existing node as last of target
     * $currentNode = BeanFactory::retrieveBean(...);
     * $targetNode = BeanFactory::retrieveBean(...);
     * $currentNode->moveAsLast($targetNode);
     *
     * + - rootNode
     *  |
     *  + - $targetNode
     *    |
     *    + - subNode1
     *    |
     *    + - subNode2
     *    |
     *    + - $currentNode
     *
     * @param Sugarcrm\Sugarcrm\Data\NestedBeanInterface $target the target.
     */
    public function moveAsLast(NestedBeanInterface $target);


    /**
     * Removes current node from tree with all children.
     * @return mixed
     */
    public function remove();
}
