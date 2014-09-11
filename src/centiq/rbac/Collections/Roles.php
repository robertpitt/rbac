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
 * Base node class, used primaraly for roles
 */
class Roles extends Nodes
{
	/**
	 * [__construct description]
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, array $roles)
	{
		/**
		 * Validate all nodes is thatof the Role type
		 */
		foreach ($roles as $index => $role)
		{
			if(!($role instanceOf \Centiq\RBAC\Entities\Role))
			{
				$roles[$index] = new \Centiq\RBAC\Entities\Role($manager, is_object($role) ? $role->id : $role['id']);
			}
		}

		parent::__construct($roles);
	}
}