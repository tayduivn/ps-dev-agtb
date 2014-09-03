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

interface NestedBeanInterface
{

    /**
     * Build tree and return all hierarchy for root
     * @return array descendants hierarchy
     */
    public function getTree();

    /**
     * Gets root nodes.
     * @return array list of root nodes.
     */
    public function getRoots();

    /**
     * Gets node children.
     * @return array list of descendants.
     */
    public function getСhildren();

    /**
     * Gets next sibling of node.
     * @return array the next sibling node.
     */
    public function getNextSibling();

    /**
     * Gets previous sibling of node.
     * @return array the prev sibling node.
     */
    public function getPrevSibling();

    /**
     * Gets parent of node.
     * @return array the parent node.
     */
    public function getParent();

    /**
     * Determines if node is descendant of target node.
     * @param Category $target the subject node.
     * @return boolean whether the node is descendant of target node.
     */
    public function isDescendantOf(NestedBeanInterface $target);

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function isRoot();

    /**
     * Inserts node as previous sibling of target.
     * @param NestedBeanInterface $node.
     */
    public function insertBefore(NestedBeanInterface $node);

    /**
     * Inserts node as next sibling of target.
     * @param NestedBeanInterface $node.
     */
    public function insertAfter(NestedBeanInterface $node);

    /**
     * Prepends node as first child.
     * @param NestedBeanInterface $node.
     */
    public function prepend(NestedBeanInterface $node);

    /**
     * Append node as last child.
     * @param NestedBeanInterface $node.
     */
    public function append(NestedBeanInterface $node);

    /**
     * Move node as previous sibling of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveBefore(NestedBeanInterface $target);

    /**
     * Move node as next sibling of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveAfter(NestedBeanInterface $target);

    /**
     * Move node as first child of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveAsFirst(NestedBeanInterface $target);

    /**
     * Move node as last child of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveAsLast(NestedBeanInterface $target);
}
