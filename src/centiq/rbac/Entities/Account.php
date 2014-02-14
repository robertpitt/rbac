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

/**
 * Account wrapper object
 */
class Account
{
	/**
	 * Account Identifer
	 * @var Integer
	 */
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
	 * Fetch the account identifer
	 * @return Integer
	 */
	public function id()
	{
		return $this->id;
	}
}