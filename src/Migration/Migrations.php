<?php

declare(strict_types=1);

/**
 * Abstract class for all Migrations
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Migration;

use PinkCrab\DB_Migration\Factory;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\Migration\Migration\Event\Activation;
use PinkCrab\Migration\Plugin_Lifecycle\Plugin_State_Controller;
use PinkCrab\Perique\Application\App;

use PinkCrab\Migration\Migration\Migration;


class Migrations {

	/**
	 * Instance of App
	 *
	 * @var App
	 */
	protected $app;

	/**
	 * Holds the instance of the plugin state controller
	 *
	 * @var Plugin_State_Controller
	 */
	protected $plugin_state_controller;

	/**
	 * Holds all Migrations
	 *
	 * @var class-string<Migration>[]
	 */
	protected $migrations = array();

	/**
	 * The migration Manager
	 *
	 * @var ?string
	 */
	protected $migration_log_key;

	/**
	 * Creates an instance of the Migrations Service.
	 *
	 * @param App $app
	 * @param string|null $migration_log_key
	 */
	public function __construct( App $app, ?string $migration_log_key = null ) {
		$this->app                     = $app;
		$this->plugin_state_controller = Plugin_State_Controller::init( $app );
		$this->migration_log_key       = $migration_log_key;
	}

	public function migration_manager(): Migration_Manager {
		return Factory::manager_with_db_delta( $this->migration_log_key );
	}

	/**
	 * Pushed an unpopulated migration to the stack
	 *
	 * @param class-string<Migration> $migration
	 * @return self
	 */
	public function add_migration( string $migration ): self {
		$this->migrations[] = $migration;
		return $this;
	}

	/**
	 * Runs the process.
	 *
	 * @return self
	 */
	public function run(): self {
		$migrations = $this->get_migrations();

		// Activation Calls
		$this->set_activation_calls( $migrations );
		return $this;
	}

	/**
	 * Get an array of all migrations, constructed
	 *
	 * @return array
	 */
	protected function get_migrations(): array {
		return array_map( array( $this->app->get_container(), 'create' ), $this->migrations );
	}

	/**
	 * Registers all actions to carry out on activation.
	 *
	 * @param Migration[] $migrations
	 * @return void
	 */
	public function set_activation_calls( array $migrations ): void {

		$migration_manager = $this->migration_manager();
		foreach ( $migrations as $migration ) {
			$migration_manager->add_migration( $migration );
		}
        dump((new Activation( $migration_manager ))->tables_to_exclude_from_seeding());
		$this->plugin_state_controller->event( new Activation( $migration_manager ) );

		// dump( $this );
	}
}
