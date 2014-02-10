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
interface NodeModel
{
	/**
	 * Create a new node
	 * @param  String  $title       Node title
	 * @param  String  $description Nodes description
	 * @param  Integer $parent      Parent node id
	 * @return Boolean
	 */
	public function create($title, $description, $parent);

	/**
	 * Get a node from the database
	 * @param  Integer $id Node id
	 * @return Object
	 */
	public function get($id);

	/**
	 * Remove a node from the database
	 * @param  Integer $id Node id
	 * @return Boolean
	 */
	public function remove($id);

	/**
	 * Update a node's meta information
	 * @param  Integer $id      ID of the node we are updating
	 * @param  array   $updates Updates we want to apply, such as title or description
	 * @return Boolean
	 */
	public function update($id, $updates = array());
}