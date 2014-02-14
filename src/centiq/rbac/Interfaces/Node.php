<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Interfaces;

/**
 * Storage interface
 */
interface Node
{
    /**
     * test if node has previous sibling
     *
     * @return bool
     */
    public function hasPrevSibling();

    /**
     * test if node has next sibling
     *
     * @return bool
     */
    public function hasNextSibling();

    /**
     * test if node has children
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * test if node has parent
     *
     * @return bool
     */
    public function hasParent();

    /**
     * gets record of prev sibling or empty record
     *
     * @return Node
     */
    public function getPrevSibling();

    /**
     * gets record of next sibling or empty record
     *
     * @return Node
     */
    public function getNextSibling();

    /**
     * gets siblings for node
     *
     * @return array array of sibling Node objects
     */
    public function getSiblings($includeNode = false);

    /**
     * gets record of first child or empty record
     *
     * @return Node
     */
    public function getFirstChild();

    /**
     * gets record of last child or empty record
     *
     * @return Node
     */
    public function getLastChild();

    /**
     * gets children for node (direct descendants only)
     *
     * @return array array of sibling Node objects
     */
    public function getChildren();

    /**
     * gets descendants for node (direct descendants only)
     *
     * @return Iterator iterator to traverse descendants from node
     */
    public function getDescendants();

    /**
     * gets record of parent or empty record
     *
     * @return Node
     */
    public function getParent();

    /**
     * gets ancestors for node
     *
     * @return Doctrine_Collection
     */
    public function getAncestors();

    /**
     * gets path to node from root, uses record::toString() method to get node names
     *
     * @param string $seperator                 path seperator
     * @param bool $includeNode                 whether or not to include node at end of path
     * @return string                           string representation of path
     */
    public function getPath($seperator = ' > ', $includeNode = false);

    /**
     * gets level (depth) of node in the tree
     *
     * @return int
     */
    public function getLevel();

    /**
     * gets number of children (direct descendants)
     *
     * @return int
     */
    public function getNumberChildren();

    /**
     * gets number of descendants (children and their children)
     *
     * @return int
     */
    public function getNumberDescendants();

    /**
     * inserts node as parent of dest record
     *
     * @return bool
     */
    public function insertAsParentOf(Node $dest);

    /**
     * inserts node as previous sibling of dest record
     *
     * @return bool
     */
    public function insertAsPrevSiblingOf(Node $dest);

    /**
     * inserts node as next sibling of dest record
     *
     * @return bool
     */
    public function insertAsNextSiblingOf(Node $dest);

    /**
     * inserts node as first child of dest record
     *
     * @return bool
     */
    public function insertAsFirstChildOf(Node $dest);

    /**
     * inserts node as first child of dest record
     *
     * @return bool
     */
    public function insertAsLastChildOf(Node $dest);

    /**
     * moves node as prev sibling of dest record
     *
     */  
    public function moveAsPrevSiblingOf(Node $dest);

    /**
     * moves node as next sibling of dest record
     *
     */
    public function moveAsNextSiblingOf(Node $dest);

    /**
     * moves node as first child of dest record
     *
     */
    public function moveAsFirstChildOf(Node $dest);

    /**
     * moves node as last child of dest record
     *
     */
    public function moveAsLastChildOf(Node $dest);

    /**
     * adds node as last child of record
     *
     */
    public function addChild(Node $record);

    /**
     * determines if node is leaf
     *
     * @return bool
     */
    public function isLeaf();

    /**
     * determines if node is root
     *
     * @return bool
     */
    public function isRoot();

    /**
     * determines if node is equal to subject node
     *
     * @return bool
     */
    public function isEqualTo(Node $subj);

    /**
     * determines if node is child of subject node
     *
     * @return bool
     */
    public function isDescendantOf($node);

    /**
     * determines if node is child of or sibling to subject node
     *
     * @return bool
     */
    public function isDescendantOfOrEqualTo($subj);

    /**
     * determines if node is valid
     *
     * @return bool
     */
    public function isValid();

    /**
     * deletes node and it's descendants
     *
     */
    public function delete();
}