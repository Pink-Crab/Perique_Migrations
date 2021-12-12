<?php

declare(strict_types=1);

/**
 * Abstract class for all Migrations
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration;

use PinkCrab\DB_Migration\Factory;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\Plugin_Lifecycle\Plugin_State_Controller;
use PinkCrab\Migration\Event\Activation;
use PinkCrab\Perique\Application\App;
use PinkCrab\Migration\Migration;


class Migrations {

	/**
	 * Holds the instance of the plugin state controller
	 *
	 * @var Plugin_State_Controller
	 */
	protected $plugin_state_controller;

	/**
	 * The migration manager instance.
	 *
	 * @var Migration_Manager
	 */
	protected $migration_manager;

	/**
	 * Access to Perique DI Container
	 *
	 * @var DI_Container
	 */
	protected $di_container;

	/**
	 * The migration Manager
	 *
	 * @var ?string
	 */
	protected $migration_log_key;

	/**
	 * Use prefix
	 *
	 * @var string|null
	 */
	protected $prefix;

	/**
	 * All migrations
	 *
	 * @var Migration[]
	 */
	protected $migrations = array();

	/**
	 * Creates an instance of the Migrations Service.
	 *
	 * @param Plugin_State_Controller $plugin_state_controller
	 */
	public function __construct( Plugin_State_Controller $plugin_state_controller, ?string $migration_log_key = null ) {
		$this->plugin_state_controller = $plugin_state_controller;
		$this->migration_log_key       = $migration_log_key;
		$this->di_container            = $plugin_state_controller->get_app()->get_container();
	}

	public function set_migration_log_key( string $log_key ): self {
		$this->migration_log_key = $log_key;
		return $this;
	}

	/**
	 * Set the migration manager instance.
	 *
	 * @param Migration_Manager $migration_manager  The migration manager instance.
	 * @return self
	 */
	public function set_migration_manager( Migration_Manager $migration_manager ):self {
		$this->migration_manager = $migration_manager;
		return $this;
	}

	/**
	 * Pushed an unpopulated migration to the stack
	 *
	 * @param class-string<Migration>|Migration $migration
	 * @return self
	 */
	public function add_migration( $migration ): self {
		if ( ! is_subclass_of( $migration, Migration::class ) ) {
			throw Migration_Exception::none_migration_type( print_r( $migration, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r, used for exception messages
		}

		$migration = $this->maybe_construct_migration( $migration );
		if ( null === $migration ) {
			throw Migration_Exception::failed_to_construct_migration( 'Invalid after construction' );
		}

		$this->migrations[] = $migration;
		return $this;
	}

	/**
	 * Attempts to create a Migration if string passed
	 *
	 * @param class-string<Migration>|Migration $migration
	 * @return Migration|null
	 */
	protected function maybe_construct_migration( $migration ): ?Migration {
		if ( is_string( $migration ) ) {
			$migration_string = $migration;
			try {
				$migration = $this->di_container->create( $migration_string );
			} catch ( \Throwable $th ) {
				throw Migration_Exception::failed_to_construct_migration( $migration_string );
			}
		}

		return is_object( $migration ) && is_a( $migration, Migration::class )
			? $migration
			: null;
	}

	/**
	 * Runs the process.
	 *
	 * @return self
	 */
	public function done(): self {
		// Set with a fallback Migration Manager if not set.
		if ( null === $this->migration_manager ) {
			$this->migration_manager = Factory::manager_with_db_delta( $this->migration_log_key );
		}
		return $this;
	}


	/**
	 * Registers all actions to carry out on activation.
	 *
	 * @param Migration[] $migrations
	 * @return void
	 */
	public function set_activation_calls( array $migrations ): void {

		$migration_manager = $this->migration_manager;
		foreach ( $migrations as $migration ) {
			$migration_manager->add_migration( $migration );
		}
		$this->plugin_state_controller->event( new Activation( $migration_manager ) );

	}

	/**
	 * Get all migrations
	 *
	 * @return Migration[]
	 */
	public function get_migrations(): array {
		return $this->migrations;
	}
}
