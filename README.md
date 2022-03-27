# Perique - Migration

A wrapper around various PinkCrab libraries which make it easier to run DB migrations from a plugin created using the Perique Framework.

![alt text](https://img.shields.io/badge/Current_Version-0.1.0-yellow.svg?style=flat " ")
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()![](https://github.com/Pink-Crab/Perique-Route/workflows/GitHub_CI/badge.svg " ")
[![codecov](https://codecov.io/gh/Pink-Crab/Perique-Route/branch/master/graph/badge.svg?token=4yEceIaSFP)](https://codecov.io/gh/Pink-Crab/Perique-Route)

## Version 0.1.0 ##

****

## Why? ##

This Module for the PinkCrab [Perique](https://perique.info) plugin framework, allows for an easy way to implement Database Migrations which are triggered and handled whenever the plugin goes through various state changes.

### Dependencies 

* [WPDB Migrations](https://github.com/Pink-Crab/WPDB_Migrations) 
* [WPDB Table Builder](https://github.com/Pink-Crab/WPDB-Table-Builder)
* [Perique Plugin Lifecycle](https://github.com/Pink-Crab/Perique_Plugin_Life_Cycle)

> Can only be used as part of the Perique Plugin Framework (WPDB Migrations can be used standalone)

****

## Setup ##

To install, you can use composer

```bash
$ composer require pinkcrab/perique-migration
```

At its core Perique Migrations uses the [Perique Plugin Lifecycle](https://github.com/Pink-Crab/Perique_Plugin_Life_Cycle) library. This allows for the definition of events that are triggered during state changes (Activation, Deactivation and Uninstall). 

> [Read the full Life Cycle Docs](https://github.com/Pink-Crab/Perique_Plugin_Life_Cycle#readme)

```php
// FILE : acme-plugin/acme-plugin.php

// Boot the app as normal
$app = (new App_Factory())->boot();

// Create an instance of the controller with instance of App.
$plugin_state_controller = new Plugin_State_Controller($app, __FILE__);

// Create an instance of the Migrations manager.
$migrations = new Migrations( $plugin_state_controller );

// Add all migrations to the manager.
$migrations->add_migration(Some_Migration_Class::class); 
$migrations->add_migration(new Someother_Migration_Class_Instance()); 

// Finialise the migrations
$migrations->done();
// Finalise the Plugin Life Cycle setup
$plugin_state_controller->finalise();
```
> Migrations can either be added as the class name or an instance. The Perique DI Container to used to construct the Migrations.

## Migration Object

Each migration must be created as an object, which extends the `PinkCrab\Perique\Migration\Migration` abstract class

## Change Log ##

* 0.1.0-rc1 Inital BETA release.
