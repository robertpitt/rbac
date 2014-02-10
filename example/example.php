<?php
/**
 * Require the autoload
 */
$loader = require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

/**
 * Require the core files
 */
require_once 'storage.php';

/**
 * Create a new storage object
 */
$storage = new ExampleStore();

/**
 * This storage method should not be implemented but here for ease of use.
 */
$storage->reset();

/**
 * Create a new manager
 */
$manager = new Centiq\RBAC\Manager($storage);

/**
 * Get an account inclosure
 */
$account = $manager->getAccount(1);

/**
 * Create some roles
 */
$st = microtime(true);

// 2nd level
$ceo = $manager->createRole("ceo", "CEO", $manager->getRootRole());

// 3rd level
$finance 	= $manager->createRole("financial",  "Financial",  $ceo);
$operations = $manager->createRole("operations", "Operations", $ceo);
$it 		= $manager->createRole("it",         "IT",         $ceo);

// 4th level
$manager->createRole("sales",         "Sales",         $finance);
$manager->createRole("margeting",     "Margeting",     $finance);
$manager->createRole("payroll",       "Payroll",       $finance);
$manager->createRole("network",       "Network",       $it);
$manager->createRole("security",      "Security",      $it);
$manager->createRole("admin",         "Admin",         $it);

/**
 * +--------------+
 * | name         |
 * +--------------+
 * | root         |
 * | -ceo         |
 * | -operations  |
 * | -sales       |
 * | ---financial |
 * | ---it        |
 * | ---margeting |
 * | --payroll    |
 * | --admin      |
 * | --network    |
 * | --security   |
 * +--------------+
 */

/**
 * Create the permissions
 */
// 2nd level
$money   = $manager->createPermission("money",   "Money",   $manager->getRootPermission());
$system  = $manager->createPermission("system",  "System",  $manager->getRootPermission());
$reports = $manager->createPermission("reports", "Reports", $manager->getRootPermission());

// 3rd level
$order 		= $manager->createPermission("order", 		"Order", 	$money);
$transfer 	= $manager->createPermission("transfer", 	"Transfer", $money);
$rooms 		= $manager->createPermission("rooms", 		"rooms", 	$system);
$users 		= $manager->createPermission("users", 		"Users", 	$system);
$general	= $manager->createPermission("users", 		"General", 	$reports);
$financial	= $manager->createPermission("financial",	"Financial",$reports);
$security	= $manager->createPermission("security",	"Security", $reports);

// 4th level
$server	= $manager->createPermission("server",	"Server", 		$rooms);
$vault	= $manager->createPermission("vault",	"Vault",  		$rooms);
$add	= $manager->createPermission("add",		"Add", 			$users);
$edit	= $manager->createPermission("edit",	"Edit",			$users);
$edit	= $manager->createPermission("pass_ch",	"Pass Change",	$users);
$edit	= $manager->createPermission("remove",	"Remove",		$users);

echo microtime(true) - $st;
/**
 * Permission Layout
 * +------------+
 * | name       |
 * +------------+
 * | root       |
 * | order      |
 * | transfer   |
 * | --system   |
 * | --money    |
 * | --reports  |
 * | --server   |
 * | --vault    |
 * | --add      |
 * | ---edit    |
 * | ---rooms   |
 * | -----users |
 * | ---remove  |
 * | ---pass_ch |
 * | -security  |
 * | -financial |
 * +------------+
 */