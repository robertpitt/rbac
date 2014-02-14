<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Entities;

class Role extends Node
{
	public function __construct(\Centiq\RBAC\Manager $manager, $id)
	{
		/**
		 * Set the initial manager and identifer
		 */
		parent::__construct($manager, "roles", $id);
	}
}