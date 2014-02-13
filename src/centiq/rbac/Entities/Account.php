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

class Account
{
	protected $identity;

	/**
	 * Constructor
	 * @param RBAC    $rbac   Core RBAC Instnace
	 * @param Integer $id User edentity
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $id)
	{
		/**
		 * Set the manager object.
		 * @var Centiq\RBAC\Manager
		 */
		$this->manager = $manager;

		/**
		 * Set the account id as the account identity
		 * @var Integer
		 */
		$this->id = $id;

		/**
		 * 1. Populate all role ids connected to this account
		 * 1a. - The rolese should be a listed of ids accross the whole tree
		 * 2. Populate all permissions connected to this account.
		 * 2a. The permissions should be a list of ids accroos the whole tre
		 */
	}

	/**
	 * Fetch the account id
	 * @return Integer
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Detect if the account is connected to a specific role
	 */
	public function hasRole($entity)
	{
	}
}