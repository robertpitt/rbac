RBAC
====

[![Build Status](https://travis-ci.org/robertpitt/rbac.png?branch=master)](https://travis-ci.org/robertpitt/rbac)

#### Important!
This package is still currently under heavy development, please do not until the package is in a stable state.

What is RBAC?
--------------

> In computer systems security, role-based access control (RBAC) is an approach to restricting system access to authorized users. It is used by the majority of enterprises with more than 500 employees,[3] and can implement mandatory access control (MAC) or discretionary access control (DAC). RBAC is sometimes referred to as role-based security.

Source: [Wikipedia](http://en.wikipedia.org/wiki/RBAC)

Design
--------------
Centiq RBAC provides NIST Level 2 Standard Hierarchical Role Based Access Control in an easy to use library that meets core php standards.

This library provides the following core abilities
* Create many Roles
* Role <> Role Inheritance
* Create many Permissions
* Permission <> Permission Inheritance

Basic Usage
--------------
```php
require 'vendor/autoload.php'

//Create a connection to the database
$connection = new PDO("mysql:dbname=rbac_main;host=localhost");

//Create a manager instance
$manager = new \Centiq\RBAC\Manager($connection);

//Fetch the root role
$root = $manager->getRootRole();

//Create a child role
$child = $root->createChild("child", "My first child role");
```


Setup and Installation
--------------
* @todo, Composer Install
* @todo, Database Installation
* @todo, Include autoloader
* @todo, Instantiate Manager Entity

Authors
--------------

* [Robert Pitt](https://github.com/robertpitt/)
