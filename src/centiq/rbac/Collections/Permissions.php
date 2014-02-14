<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Collections;

/**
 * Base node class, used primaraly for roles and permissions
 */
class Permissions extends Nodes
{
	/**
	 * [__construct description]
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, array $permissions)
	{
		/**
		 * Validate all nodes is thatof the Role type
		 */
		foreach ($permissions as $index => $permission)
		{
			if(!($permission instanceOf \Centiq\RBAC\Entities\Permission))
			{
				$permissions[$index] = new \Centiq\RBAC\Entities\Permission($manager, is_object($permission) ? $permission->id : $permission['id']);
			}
		}

		parent::__construct($permissions);
	}
}