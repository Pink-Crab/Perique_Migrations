<?php

declare(strict_types=1);

/**
 * Abstract class for all Migrations
 *
 * @package PinkCrab\Perique\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 1.0.0
 */

namespace PinkCrab\Perique\Migration\Module;

use PinkCrab\DB_Migration\Factory;
use PinkCrab\Perique\Migration\Event\Uninstall;
use PinkCrab\Perique\Migration\Migration_Exception;
use PinkCrab\Perique\Interfaces\Module;
use PinkCrab\Perique\Migration\Migration;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Loader\Hook_Loader;
use PinkCrab\Perique\Migration\Event\Activation;
use PinkCrab\Perique\Migration\Event\Deactivation;
use PinkCrab\Plugin_Lifecycle\Plugin_Life_Cycle;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\Plugin_Lifecycle\Plugin_State_Change;

final class Perique_Migrations implements Module {

	/** @var class-string<Migration>[] */
	private array $migrations         = array();
	private string $migration_log_key = 'pinkcrab_migration_log';

	/**
	 * Add a migration to the list of migrations to run.
	 *
	 * @param class-string<Migration> $migration
	 * @return self
	 */
	public function add_migration( string $migration ): self {
		if ( ! is_subclass_of( $migration, Migration::class ) ) {
			throw Migration_Exception::none_migration_type( $migration );
		}

		$this->migrations[] = $migration;
		return $this;
	}

	/**
	 * Set the migration log key.
	 *
	 * @param string $migration_log_key
	 * @return self
	 */
	public function set_migration_log_key( string $migration_log_key ): self {
		$this->migration_log_key = \sanitize_title( $migration_log_key );
		return $this;
	}

	/**
	 * Get the migration log key.
	 *
	 * @return string
	 */
	public function get_migration_log_key(): string {
		return $this->migration_log_key;
	}


	/**
	 * Used to create the controller instance and register the hook call, to trigger.
	 *
	 * @pram App_Config $config
	 * @pram Hook_Loader $loader
	 * @pram DI_Container $di_container
	 * @return void
	 */
	public function pre_boot( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed
		// Set the migration manager.
		$migration_manager = Factory::manager_with_db_delta( $this->migration_log_key );

		// Add the migrations to the manager.
		foreach ( $this->generate_migrations( $di_container ) as $migration ) {
			$migration_manager->add_migration( $migration );
		}

		// Add to the events.
		if ( \class_exists( Plugin_Life_Cycle::class ) ) {
			add_filter( Plugin_Life_Cycle::EVENT_LIST, $this->generate_migration_events_callback( $migration_manager ) );
		}

	}

	/**
	 * Generates the callback for adding the migration events to the plugin life cycle.
	 *
	 * @param Migration_Manager $migration_manager
	 * @return \Closure(array<Plugin_State_Change>):array<Plugin_State_Change>
	 */
	protected function generate_migration_events_callback( Migration_Manager $migration_manager ): \Closure {
		/**
		 * @param array<Plugin_State_Change> $events
		 * @return array<Plugin_State_Change>
		 */
		return function( array $events ) use ( $migration_manager ): array {
			static $cached = null;
			if ( is_null( $cached ) ) {
				$cached = array(
					new Activation( $migration_manager ),
					new Deactivation( $migration_manager ),
					new Uninstall( $migration_manager ),
				);
			}
			return array_merge( $events, $cached );
		};
	}

	/**
	 * Generate Migrations from the list of migrations.
	 *
	 * @param DI_Container $container
	 * @return array<Migration>
	 * @throws Migration_Exception
	 */
	protected function generate_migrations( DI_Container $container ): array {
		$migrations = array();
		foreach ( $this->migrations as $migration ) {
			if ( is_string( $migration ) ) {
				try {
					$instance = $container->create( $migration );
				} catch ( \Throwable $th ) {
					throw Migration_Exception::failed_to_construct_migration( $migration );
				}

				// Add to the list if valid.
				if ( ! is_object( $instance ) || ! is_a( $instance, Migration::class ) ) {
					throw Migration_Exception::failed_to_construct_migration( $migration );
				}

				$migrations[] = $instance;
			}
		}
		return $migrations;
	}

	## Unused methods

	/** @inheritDoc */
	public function pre_register( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {} // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed

	/** @inheritDoc */
	public function post_register( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {} // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed

	/** @inheritDoc */
	public function get_middleware(): ?string {
		return null;
	}
}
