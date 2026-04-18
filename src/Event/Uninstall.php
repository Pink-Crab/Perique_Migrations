<?php

declare(strict_types=1);

/**
 * Deactivation event to be launched on Uninstall.
 *
 * @package PinkCrab\Perique\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Perique\Migration\Event;

use PinkCrab\Perique\Migration\Migration;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\DB_Migration\Database_Migration;
use PinkCrab\Plugin_Lifecycle\State_Event\Uninstall as State_Events_Uninstall;

class Uninstall implements State_Events_Uninstall {

	/**
	 * Array of tables to be dropped.
	 *
	 * @var Migration[]
	 */
	protected array $tables = array();

	/**
	 * The migration log key to clear after dropping tables.
	 *
	 * @var string
	 */
	protected string $migration_log_key;

	public function __construct( Migration_Manager $migration_manager ) {
		$this->set_tables( $migration_manager->get_migrations() );
		$this->migration_log_key = $migration_manager->migration_log()->get_log_key();
	}


	/**
	 * Set the table to remove
	 *
	 * @param Database_Migration[] $migrations
	 * @return void
	 */
	public function set_tables( array $migrations ): void {
		$this->tables = \array_filter(
			$migrations,
			fn( Database_Migration $migration ) => is_a( $migration, Migration::class ) && $migration->drop_on_uninstall()
		);
	}



	/**
	 * Invokes the run method.
	 *
	 * @return void
	 */
	public function __invoke(): void {
		$this->run();
	}

	/**
	 * Runs the dropping of all valid tables.
	 *
	 * The down() hooks and the table drops run inside a try block; the
	 * migration log is removed in the finally so it is always cleaned up,
	 * even if a drop fails part-way through, so a subsequent reinstall
	 * starts from a clean manifest.
	 *
	 * @return void
	 */
	public function run(): void {
		// If no tables, return.
		if ( empty( $this->tables ) ) {
			return;
		}

		try {
			$this->run_down_hooks();
			$this->drop_tables();
		} finally {
			$this->remove_migration_log();
		}
	}

	/**
	 * Fire the down() hook on each migration flagged for drop-on-uninstall,
	 * before the table is actually dropped so the hook can still read from
	 * it. $this->tables has already been filtered to just the migrations
	 * with drop_on_uninstall() === true.
	 *
	 * @return void
	 */
	private function run_down_hooks(): void {
		foreach ( $this->tables as $migration ) {
			if ( $migration instanceof Migration ) {
				$migration->down();
			}
		}
	}

	/**
	 * Drops all tables.
	 *
	 * @return void
	 */
	protected function drop_tables() {
		/**
		 * @var \wpdb $wpdb
		*/
		global $wpdb;

		// Temp disable warnings.
		$original_state = (bool) $wpdb->suppress_errors;
		$wpdb->suppress_errors( true );

		foreach ( $this->tables as $table ) {
			$table_name = $table->get_table_name();
			$wpdb->get_results( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Reset warnings to initial state.
		$wpdb->suppress_errors( $original_state );
	}

	/**
	 * Delete the migration log.
	 *
	 * @return void
	 */
	protected function remove_migration_log(): void {
		\delete_option( $this->migration_log_key );
	}
}
